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

/**
 * Maskiert die letzten $numChars Zeichen einer Zeichenkette mit '*'.
 * Wenn die Zeichenkette kürzer ist als $numChars, wird komplett maskiert.
 */
function maskString(string $input, int $numChars = 6): string
{
    $len = mb_strlen($input);
    if ($len <= $numChars) {
        return str_repeat('*', $len);
    }
    $visible = mb_substr($input, 0, $len - $numChars);
    return $visible . str_repeat('*', $numChars);
}

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

    $stmt = $pdo->prepare('
    SELECT id, username, password_hash, is_verified, role
    FROM users
    WHERE (username = :user OR email = :email)
    LIMIT 1
    ');
    $stmt->execute([
        'user' => $usernameOrEmail,
        'email' => $usernameOrEmail
    ]); 

    $user = $stmt->fetch();

    if (!$user) {
        // fehlgeschlagener Login – User nicht gefunden
        $pdo->prepare(
            'INSERT INTO login_logs (user_id, ip_address, success, reason)
             VALUES (NULL, :ip, FALSE, :reason)'
        )->execute([
            'ip'     => maskString($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 12),
            'reason' => 'user_not_found'
        ]);
        header('Location: index.php?error=user_not_found');
        exit;
    }
    
    if ((int)$user['is_verified'] !== 1) {
        // fehlgeschlagener Login – nicht verifiziert
        $pdo->prepare(
            'INSERT INTO login_logs (user_id, ip_address, success, reason)
             VALUES (:uid, :ip, FALSE, :reason)'
        )->execute([
            'uid'    => (int)$user['id'],
            'ip'     => maskString($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 12),
            'reason' => 'not_verified'
        ]);
        header('Location: index.php?error=not_verified');
        exit;
    }
    
    if (!Password::verify(new HiddenString($password), $user['password_hash'], $key)) {
        // fehlgeschlagener Login – falsches Passwort
        $pdo->prepare(
            'INSERT INTO login_logs (user_id, ip_address, success, reason)
             VALUES (:uid, :ip, FALSE, :reason)'
        )->execute([
            'uid'    => (int)$user['id'],
            'ip'     => maskString($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 12),
            'reason' => 'wrong_password'
        ]);
        header('Location: index.php?error=wrong_password');
        exit;
    }

    // IP ermitteln
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';


    // IP anonymisieren – letzte 12 Zeichen maskieren
    $maskedIp = maskString($ip, 12);

    try {
        $insertLog = $pdo->prepare(
            'INSERT INTO login_logs (user_id, ip_address, success) VALUES (:uid, :ip, TRUE)'
        );
        $insertLog->execute([
            'uid' => (int)$user['id'],
            'ip'  => $maskedIp,
        ]);
    } catch (\Throwable $e) {
        $log->warning('Konnte erfolgreichen Login-Log nicht speichern: ' . $e->getMessage());
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']      = $user['role']; // 'admin' | 'moderator' | 'user'
    $_SESSION['last_activity'] = time();

    // erfolgreich eingeloggt
    $_SESSION['flash'] = [
    'type'    => 'success',
    'message' => 'Login erfolgreich! Du wirst weitergeleitet…'
    ];
    // ➔ Weiterleitung zum Dashboard
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
