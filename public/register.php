<?php
declare(strict_types=1);

// 0) JSON-Response und Buffer starten
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(0);
ob_start();

// DEBUG-Flag: in Produktion auf false lassen
const DEBUG = true;

// Basis-Antwort
$response = ['success' => false];

// 1) Core-Dateien einbinden
require_once __DIR__ . '/../includes/db.inc.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use ParagonIE\Halite\Password;
use PDO;

// 2) reCAPTCHA-Funktionen einbinden
require_once __DIR__ . '/../includes/recaptcha.inc.php';

try {
    // 3) Sicherstellen, dass sodium verfügbar ist
    if (!extension_loaded('sodium')) {
        throw new \RuntimeException(
            'PHP-Extension "sodium" fehlt. Bitte in php.ini aktivieren.'
        );
    }

    // 4) Logger konfigurieren
    $log = new Logger('user_registration');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../logs/register.log', Level::Debug));
    $log->debug('register.php gestartet');

    // 5) .env laden
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $config = require __DIR__ . '/../includes/config.inc.php';

    // 6) Halite-Schlüssel importieren
    $b64 = $_ENV['HALITE_KEYFILE_BASE64']
         ?? $_SERVER['HALITE_KEYFILE_BASE64']
         ?? getenv('HALITE_KEYFILE_BASE64')
         ?? '';
    if (empty($b64)) {
        throw new \RuntimeException('Verschlüsselungsschlüssel nicht gefunden.');
    }
    $raw = base64_decode($b64, true);
    if ($raw === false) {
        throw new \RuntimeException('Base64-Decode des Schlüssels fehlgeschlagen.');
    }
    $key = KeyFactory::importEncryptionKey(new HiddenString($raw));

    // 7) DB-Verbindung aufbauen
    $pdo = DbFunctions::db_connect();

    // 8) reCAPTCHA v3 prüfen und loggen (Action "register")
    $token  = $_POST['recaptcha_token'] ?? '';
    $secret = (string)$config['recaptcha_secret']; 
    if (!recaptcha_verify($pdo, $token, $secret, 0.5)) {
        $response['errors']['recaptcha'] = 'reCAPTCHA-Validierung fehlgeschlagen. Bitte erneut versuchen.';
        throw new \DomainException('reCAPTCHA fehlgeschlagen');
    }

    // 9) Eingaben sammeln und validieren
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pw       = $_POST['password'] ?? '';
    $pw2      = $_POST['password_confirm'] ?? '';
    $errors   = [];

    if ($username === '') {
        $errors['username'] = 'Bitte wähle einen Benutzernamen.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Bitte gib eine gültige E-Mail-Adresse an.';
    }
    if (mb_strlen($pw) < 8) {
        $errors['password'] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    }
    if ($pw !== $pw2) {
        $errors['password_confirm'] = 'Passwörter stimmen nicht überein.';
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Validierungsfehler');
    }

    // 10) Existenz-Checks (Username, E-Mail)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $errors['username'] = 'Dieser Benutzername ist bereits vergeben.';
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert.';
    }
    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Benutzer bereits vorhanden');
    }

    // 11) Passwort hashen & in DB einfügen
    $hash = Password::hash(new HiddenString($pw), $key);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, is_verified)
         VALUES (?, ?, ?, FALSE)'
    );
    if (!$stmt->execute([$username, $email, (string)$hash])) {
        throw new \RuntimeException('INSERT fehlgeschlagen.');
    }

    // 12) Verifikations-Mail senden
    try {
        require_once __DIR__ . '/../includes/verification.inc.php';
        sendVerificationEmail(
            $pdo,
            $username,
            $email,
            $_SERVER['HTTP_HOST'],
            $log
        );
        $response['message'] = 'Registrierung fast abgeschlossen! Bitte bestätige deine E-Mail.';
    } catch (\Exception $e) {
        $response['message'] = 'Registrierung erfolgreich, aber Bestätigungs-Mail konnte nicht gesendet werden.';
        $log->warning('Verifikations-Mail fehlgeschlagen', ['error' => $e->getMessage()]);
    }

    // 13) Erfolg
    $response['success'] = true;

} catch (\DomainException $e) {
    if (!isset($response['errors'])) {
        $response['message'] = $e->getMessage();
    }
} catch (\Throwable $e) {
    if (isset($log)) {
        $log->error('Uncaught Exception in register.php', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
    }
    $response['message'] = DEBUG
        ? $e->getMessage()
        : 'Interner Serverfehler. Bitte später erneut versuchen.';
}

// 14) Finale JSON-Antwort
ob_clean();
echo json_encode($response);
exit;
