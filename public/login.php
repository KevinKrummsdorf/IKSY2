<?php

declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Models/Database.php';
require_once __DIR__ . '/../src/Models/UserModel.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

$log = LoggerFactory::get('login');

try {
    $identifier = trim($_POST['username_or_email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $ip         = getClientIp();
    $maskedIp   = maskIp($ip);

    if ($identifier === '' || $password === '') {
        throw new DomainException('Felder leer');
    }

    $controller = new AuthController();
    $result = $controller->login($identifier, $password);

    if (!$result['success']) {
        $log->warning('Login failed', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => $result['message'],
        ];
        header('Location: index.php');
        exit;
    }

    $user   = $result['user'];
    $userId = (int)$user['id'];

    // 6) Wenn 2FA aktiviert, weiterleiten
    if (UserModel::isTwoFAEnabled($user['username'])) {
        require_once __DIR__ . '/../includes/2fa.inc.php';
        $_SESSION['2fa_user']        = $user['username'];
        $_SESSION['user_id_pending'] = $userId;
        $_SESSION['role_pending']    = $user['role'] ?? 'user';

        $_SESSION['flash'] = [
            'type'    => 'info',
            'message' => 'Bitte gib deinen 2FA-Code ein.',
            'context' => '2fa_prompt'
        ];
        header('Location: 2fa_prompt.php');
        exit;
    }

    // 7) Login erfolgreich: Zeit und Logs
    UserModel::updateLastLogin($userId);
    $log->info('User logged in successfully', [
        'ip'         => $maskedIp,
        'identifier' => $identifier,
    ]);

    // 8) Session setzen
    $_SESSION['user_id']       = $userId;
    $_SESSION['username']      = $user['username'];
    $_SESSION['role']          = $user['role'] ?? 'user';
    $_SESSION['2fa_passed']    = true;
    $_SESSION['last_activity'] = time();

    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => 'Login erfolgreich! Du wirst weitergeleitet.',
        'context' => 'login',
    ];
    header('Location: index.php');
    exit;

} catch (DomainException $e) {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Bitte fülle alle Felder aus.',
    ];
    header('Location: index.php');
    exit;

} catch (Throwable $e) {
    $log->error('Unerwarteter Fehler bei Login', ['error' => $e->getMessage()]);
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Interner Serverfehler. Bitte versuche es später erneut.',
    ];
    header('Location: index.php');
    exit;
}
