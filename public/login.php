<?php
declare(strict_types=1);

session_start();

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';


use ParagonIE\Halite\Password;
use ParagonIE\HiddenString\HiddenString;

$monolog = getLogger('login');
$log     = new MonologLoggerAdapter($monolog);

try {
    $key             = $config['halite_key'];
    $identifier      = trim($_POST['username_or_email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $ip              = getClientIp();
    $maskedIp        = maskIp($ip);

    if ($identifier === '' || $password === '') {
        throw new DomainException('Felder leer');
    }

    // 1) User holen
    $user = DbFunctions::fetchUserByIdentifier($identifier);

    if (!$user) {
        DbFunctions::insertLoginLog(null, $maskedIp, false, 'user_not_found');
        $log->warning('Login attempt with non-existing user', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Benutzer nicht gefunden. Bitte überprüfe Benutzername oder E-Mail.',
        ];
        header('Location: index.php');
        exit;
    }

    // 2) Verifiziert?
    if ((int)$user['is_verified'] !== 1) {
        DbFunctions::insertLoginLog((int)$user['id'], $maskedIp, false, 'not_verified');
        $log->warning('Login attempt with unverified account', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'warning',
            'message' => 'Dein Account ist noch nicht verifiziert. Bitte prüfe deine E-Mails.',
        ];
        header('Location: index.php');
        exit;
    }

    // 3) Passwort prüfen
    if (!Password::verify(new HiddenString($password), $user['password_hash'], $key)) {
        DbFunctions::insertLoginLog((int)$user['id'], $maskedIp, false, 'wrong_password');
        $log->warning('Login attempt with wrong password', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Falsches Passwort. Bitte versuche es erneut.',
        ];
        header('Location: index.php');
        exit;
    }

    // 4) Login erfolgreich: last_login updaten und Log schreiben
    DbFunctions::updateLastLogin((int)$user['id']);
    DbFunctions::insertLoginLog((int)$user['id'], $maskedIp, true);
    $log->info('User logged in successfully', [
        'ip'         => $maskedIp,
        'identifier' => $identifier,
    ]);

    // 5) Session setzen
    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['role']          = $user['role'] ?? 'user';
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
