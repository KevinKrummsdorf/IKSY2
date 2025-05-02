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

try {
    // 3) Sicherstellen, dass sodium verfügbar ist
    if (!extension_loaded('sodium')) {
        throw new \RuntimeException(
            'PHP-Extension "sodium" fehlt. Bitte in php.ini aktivieren.'
        );
    }

    // 6) Logger konfigurieren (optional, kann bei Bedarf deaktiviert werden)
    $log = new Logger('user_registration');
    $log->debug('register.php geladen und Monolog konfiguriert');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../logs/register.log', Level::Debug));

    // 4) .env laden
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    //Verifikations-Logik einbinden
    require_once __DIR__ . '/../includes/verification.inc.php';

    // 5) Halite-Schlüssel importieren
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

    // 8) Eingaben sammeln und validieren
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pw       = $_POST['password'] ?? '';
    $pw2      = $_POST['password_confirm'] ?? '';
    $errors   = [];

    // … hier dein bestehendes Validierungs-Logik … //

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Validierungsfehler');
    }

    // 9) Existenz-Checks (Username, E-Mail)
    // … bestehender Code für SELECT COUNT(*) … //

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new \DomainException('Benutzer bereits vorhanden');
    }

    // 10) Passwort hashen & in DB einfügen
    $hash = Password::hash(new HiddenString($pw), $key);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, is_verified)
         VALUES (?, ?, ?, FALSE)'
    );
    $res = $stmt->execute([
        $username,
        $email,
        (string)$hash
    ]);
    if (!$res) {
        throw new \RuntimeException('INSERT fehlgeschlagen.');
    }

    // 11) Markiere Erfolg
    // ... nach Monolog-Initialisierung und INSERT ...
    $response['success'] = true;
    $log->info('Registrierung erfolgreich', ['username' => $username]);

    try {
        sendVerificationEmail(
            $pdo,
            $username,
            $email,
            $_SERVER['HTTP_HOST'],
            $log                // <- hier den Monolog-Logger übergeben
    );
    $response['message'] = 'Registrierung fast abgeschlossen! ...';
    } catch (\Exception $e) {
        $response['message'] = '…, aber die Verifizierungs-Mail konnte nicht versendet werden.';
        $log->warning('Verifikations-Mail-Versand fehlgeschlagen', ['error' => $e->getMessage()]);
    }


} catch (\DomainException $e) {
    // Benutzer- oder Validierungsfehler
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

// 13) Finale JSON-Antwort
ob_clean();
echo json_encode($response);
exit;
