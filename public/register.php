<?php
declare(strict_types=1);

// 0) JSON-Response und Buffer starten
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(0);
ob_start();

// DEBUG-Flag: false in Produktion
const DEBUG = false;

// Basis-Antwort
$response = ['success' => false];

require_once __DIR__ . '/../includes/db.inc.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Password;
use ParagonIE\HiddenString\HiddenString;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

try {
    // 1) Ensure sodium extension is available
    if (!extension_loaded('sodium')) {
        throw new \RuntimeException(
            'PHP-Extension "sodium" fehlt. Bitte in php.ini aktivieren: ' .
            'extension=sodium oder extension=php_sodium.dll'
        );
    }

    // 2) Load .env
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // 3) Read HALITE_KEYFILE_BASE64 from environment
    $b64 = $_ENV['HALITE_KEYFILE_BASE64']
         ?? $_SERVER['HALITE_KEYFILE_BASE64']
         ?? getenv('HALITE_KEYFILE_BASE64')
         ?? '';

    if (empty($b64)) {
        throw new \RuntimeException('Verschlüsselungsschlüssel nicht in HALITE_KEYFILE_BASE64 gefunden.');
    }

    // 4) Decode Base64 and import Halite key
    $raw = base64_decode($b64, true);
    if ($raw === false) {
        throw new \RuntimeException('Base64-Decode des Schlüssels fehlgeschlagen.');
    }
    $key = KeyFactory::importEncryptionKey(new HiddenString($raw));

    // 5) Logger konfigurieren
    $log = new Logger('user_registration');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../logs/register.log', Level::Warning));

    // 6) Verbindung zur Datenbank aufbauen
    $pdo = DbFunctions::db_connect();

    // 7) Eingaben holen und validieren
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pw       = $_POST['password'] ?? '';
    $pw2      = $_POST['password_confirm'] ?? '';
    $errors   = [];

    if ($username === '') {
        $errors['username'] = 'Bitte geben Sie einen Benutzernamen ein.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors['username'] = 'Benutzername muss 3–20 Zeichen lang sein und darf nur a–Z, 0–9 und _ enthalten.';
    }

    if ($email === '') {
        $errors['email'] = 'Bitte geben Sie eine E-Mail-Adresse ein.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
        $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse (max. 255 Zeichen) ein.';
    }

    if ($pw === '') {
        $errors['password'][] = 'Bitte geben Sie ein Passwort ein.';
    } else {
        $len = mb_strlen($pw);
        if ($len < 8 || $len > 128) {
            $errors['password'][] = 'Passwort muss 8–128 Zeichen lang sein.';
        }
        if (!preg_match('/[A-Z]/', $pw)) {
            $errors['password'][] = 'Passwort muss mindestens einen Großbuchstaben enthalten.';
        }
        if (!preg_match('/[a-z]/', $pw)) {
            $errors['password'][] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten.';
        }
        if (!preg_match('/[0-9]/', $pw)) {
            $errors['password'][] = 'Passwort muss mindestens eine Zahl enthalten.';
        }
        if (!preg_match('/[\W_]/', $pw)) {
            $errors['password'][] = 'Passwort muss mindestens ein Sonderzeichen enthalten.';
        }
    }

    if ($pw2 === '') {
        $errors['password_confirm'] = 'Bitte bestätigen Sie Ihr Passwort.';
    } elseif ($pw !== $pw2) {
        $errors['password_confirm'] = 'Die Passwörter stimmen nicht überein.';
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Validierungsfehler');
    }

    // 8) Existenz-Checks
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $cntUser = (int) $stmt->fetchColumn();

    if ($cntUser > 0) {
        $errors['username'] = 'Benutzername ist bereits vergeben.';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $cntMail = (int) $stmt->fetchColumn();

    if ($cntMail > 0) {
        $errors['email'] = 'E-Mail-Adresse ist bereits registriert.';
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Benutzer bereits vorhanden');
    }

    // 9) Passwort hashen und in DB einfügen
    $hash = Password::hash(new HiddenString($pw), $key);

    // WICHTIG: explizit in einen String umwandeln!
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_verified) VALUES (?, ?, ?, 0)');
    $res = $stmt->execute([
        $username,
        $email,
        (string) $hash   // <-- Hier casten!
    ]);

    if (!$res) {
        throw new \RuntimeException('INSERT fehlgeschlagen.');
    }

    $response['success'] = true;
    $log->info('Registrierung erfolgreich', ['username' => $username]);

} catch (\DomainException $e) {
    // Validierungs- oder Duplicate-Fehler
    if (empty($response['errors'])) {
        $response['message'] = $e->getMessage();
    }
} catch (\Throwable $e) {
    // Schwere Fehler
    if (isset($log)) {
        $log->error('Uncaught Exception', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
    }
    $response['message'] = DEBUG
        ? $e->getMessage()
        : 'Interner Serverfehler. Bitte später erneut versuchen.';
}

// 10) Nur das finale JSON senden
ob_clean();
echo json_encode($response);
exit;
