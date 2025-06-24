<?php

declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';

$log = LoggerFactory::get('login');

try {
    $identifier = trim($_POST['username_or_email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $ip         = getClientIp();
    $maskedIp   = maskIp($ip);

    if ($identifier === '' || $password === '') {
        throw new DomainException('Felder leer');
    }

    // 1) User holen
    $user = DbFunctions::fetchUserByIdentifier($identifier);

    if (!$user) {
        $log->warning('Login attempt with non-existing user', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Benutzer nicht gefunden. Bitte überprüfe Benutzername oder E-Mail.',
        ];
        header('Location: index');
        exit;
    }

    $userId = (int)$user['id'];

    // 2) Konto gesperrt?
    if (DbFunctions::isAccountLocked($userId)) {
        $log->warning('Login attempt on locked account', [
            'ip'   => $maskedIp,
            'user' => $user['username'],
        ]);
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Dein Account ist vorübergehend gesperrt. Bitte versuche es später erneut.',
        ];
        header('Location: index');
        exit;
    }

    // 3) Verifiziert?
    if ((int)$user['is_verified'] !== 1) {
        $log->warning('Login attempt with unverified account', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);
        $_SESSION['flash'] = [
            'type'    => 'warning',
            'message' => 'Dein Account ist noch nicht verifiziert. Bitte prüfe deine E-Mails.',
        ];
        header('Location: index');
        exit;
    }

    // 4) Passwort prüfen
    if (!verifyPassword($password, $user['password_hash'])) {
        DbFunctions::updateFailedAttempts($userId);

        $log->warning('Login attempt with wrong password', [
            'ip'         => $maskedIp,
            'identifier' => $identifier,
        ]);

        // Optional: direkt sperren ab X Fehlversuchen (z. B. 5)
        $attempts = DbFunctions::fetchValue('SELECT failed_attempts FROM user_security WHERE user_id = :id', [':id' => $userId]);
        if ($attempts >= 5) {
            DbFunctions::lockAccount($userId, 15); // z. B. 15 Minuten Sperre
        }

        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Falsches Passwort. Bitte versuche es erneut.',
        ];
        header('Location: index');
        exit;
    }

    // 5) Login erfolgreich – Fehlversuche zurücksetzen
    DbFunctions::resetFailedAttempts($userId);

    // 6) Wenn 2FA aktiviert, weiterleiten
    if (DbFunctions::isTwoFAEnabled($user['username'])) {
        require_once __DIR__ . '/../includes/2fa.inc.php';
        $_SESSION['2fa_user']        = $user['username'];
        $_SESSION['user_id_pending'] = $userId;
        $_SESSION['role_pending']    = $user['role'] ?? 'user';

        $_SESSION['flash'] = [
            'type'    => 'info',
            'message' => 'Bitte gib deinen 2FA-Code ein.',
            'context' => '2fa_prompt'
        ];
        header('Location: 2fa_prompt');
        exit;
    }

    // 7) Login erfolgreich: Zeit und Logs
    DbFunctions::updateLastLogin($userId);
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
    header('Location: index');
    exit;

} catch (DomainException $e) {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Bitte fülle alle Felder aus.',
    ];
    header('Location: index');
    exit;

} catch (Throwable $e) {
    $log->error('Unerwarteter Fehler bei Login', ['error' => $e->getMessage()]);
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Interner Serverfehler. Bitte versuche es später erneut.',
    ];
    header('Location: index');
    exit;
}
