<?php
declare(strict_types=1);

session_start();

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
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $b64 = $_ENV['HALITE_KEYFILE_BASE64'] ?? getenv('HALITE_KEYFILE_BASE64') ?? '';
    if (empty($b64)) {
        throw new \RuntimeException('Verschlüsselungsschlüssel fehlt.');
    }
    $key = KeyFactory::importEncryptionKey(new HiddenString(base64_decode($b64, true)));

    $log = new Logger('user_login');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../logs/login.log', Level::Warning));

    $pdo = DbFunctions::db_connect();

    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        throw new \DomainException('Bitte alle Felder ausfüllen.');
    }

    $stmt = $pdo->prepare('SELECT id, username, password_hash, is_verified FROM users WHERE username = :user OR email = :email LIMIT 1');
    $stmt->execute([
        'user' => $usernameOrEmail,
        'email' => $usernameOrEmail
    ]);    
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_verified'] !== 1) {
        throw new \DomainException('Benutzer nicht gefunden oder nicht verifiziert.');
    }

    if (!Password::verify(
        new HiddenString($password),
        $user['password_hash'],
        $key
    )) {
        throw new \DomainException('Falsches Passwort.');
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['last_activity'] = time();

    // Erfolgreicher Login ➔ Weiterleitung zum Dashboard
    header('Location: index.php?login=success');
    exit;

} catch (\DomainException $e) {
    // Fehlerhafte Eingaben ➔ zurück zur Startseite mit Fehlercode
    header('Location: index.php?error=1');
    exit;
} catch (\Throwable $e) {
    // Schwerwiegender Fehler ➔ zurück zur Startseite
    header('Location: index.php?error=2');
    exit;
}
?>
