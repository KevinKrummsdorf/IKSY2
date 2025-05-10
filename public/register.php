<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Password;
use ParagonIE\HiddenString\HiddenString;

$monolog  = getLogger('register');
$log      = new MonologLoggerAdapter($monolog);
$response = ['success' => false];

// PDO-Verbindung bereitstellen
$pdo = DbFunctions::db_connect();

try {
    if (!extension_loaded('sodium')) {
        $log->error('PHP-Extension "sodium" nicht verfügbar.');
        throw new RuntimeException('Die PHP-Extension "sodium" ist nicht verfügbar.');
    }

    $log->info('Registrierung gestartet');

    // Halite-Key laden
    $rawKey = base64_decode($_ENV['HALITE_KEYFILE_BASE64'] ?? '', true);
    if (!$rawKey) {
        $log->error('HALITE_KEYFILE_BASE64 fehlt oder ungültig.');
        throw new RuntimeException('Server-Konfigurationsfehler.');
    }
    $key = KeyFactory::importEncryptionKey(new HiddenString($rawKey));

    // reCAPTCHA prüfen
    $token  = $_POST['recaptcha_token'] ?? '';
    $secret = $config['recaptcha']['secret_key'];
    if (!recaptcha_verify($pdo, $token, $secret, $config['recaptcha']['min_score'])) {
        $response['errors']['recaptcha'] = 'reCAPTCHA fehlgeschlagen.';
        $log->warning('reCAPTCHA-Validierung fehlgeschlagen', ['token' => $token]);
        throw new DomainException('reCAPTCHA ungültig.');
    }

    // Eingaben validieren
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $pw       = $_POST['password']      ?? '';
    $pw2      = $_POST['password_confirm'] ?? '';
    $errors   = [];

    if ($username === '') {
        $errors['username'] = 'Benutzername erforderlich.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ungültige E-Mail.';
    }
    if (mb_strlen($pw) < 8) {
        $errors['password'] = 'Mindestens 8 Zeichen.';
    }
    if ($pw !== $pw2) {
        $errors['password_confirm'] = 'Passwörter stimmen nicht überein.';
    }
    if ($errors) {
        $response['errors'] = $errors;
        throw new DomainException('Ungültige Eingaben.');
    }

    // Doppelte prüfen
    if (DbFunctions::countWhere('users', 'username', $username) > 0) {
        $errors['username'] = 'Benutzername vergeben.';
    }
    if (DbFunctions::countWhere('users', 'email', $email) > 0) {
        $errors['email'] = 'E-Mail vergeben.';
    }
    if ($errors) {
        $response['errors'] = $errors;
        $log->warning('Doppelte Benutzerprüfung fehlgeschlagen', ['username' => $username, 'email' => $email]);
        throw new DomainException('Benutzer existiert bereits.');
    }

    // Passwort hashen
    $hash = Password::hash(new HiddenString($pw), $key);

    // Transaktion starten
    DbFunctions::beginTransaction();

    // Nutzer anlegen
    $userId = DbFunctions::insertUser($username, $email, (string)$hash);
    $log->info('User angelegt', ['user_id' => $userId]);

    // Default-Rolle (ID 3) zuweisen
    DbFunctions::assignRole($userId, 3);
    $log->info('Rolle zugewiesen', ['user_id' => $userId, 'role_id' => 3]);

    // Commit
    DbFunctions::commit();

    // Verifikations-Mail
    try {
        require_once __DIR__ . '/../includes/verification.inc.php';
        sendVerificationEmail($pdo, $userId, $username, $email);
        $response['message'] = 'Bestätigungs-E-Mail gesendet.';
    } catch (Exception $e) {
        $response['message'] = 'Registrierung gespeichert, aber Mailversand fehlgeschlagen.';
        $log->error('Mailversand fehlgeschlagen', [
            'user_id'  => $userId,
            'username' => $username,
            'email'    => $email,
            'error'    => $e->getMessage(),
        ]);
    }

    $response['success'] = true;
    $log->info('Registrierung erfolgreich', ['username' => $username, 'email' => $email]);

} catch (DomainException $e) {
    if (!isset($response['errors'])) {
        $response['message'] = $e->getMessage();
        $log->warning('Domain-Fehler bei Registrierung', ['msg' => $e->getMessage()]);
    }
    DbFunctions::rollBack();

} catch (Throwable $e) {
    $log->error('Unerwarteter Fehler bei Registrierung', ['error' => $e->getMessage()]);
    $response['message'] = DEBUG
        ? $e->getMessage()
        : 'Interner Serverfehler. Bitte später erneut versuchen.';
    DbFunctions::rollBack();
}

echo json_encode($response);
exit;
