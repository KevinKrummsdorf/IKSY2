<?php
require_once 'db.php';
require_once 'vendor/autoload.php'; // Halite, phpdotenv und Monolog laden

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Password;
use Dotenv\Dotenv;
use ParagonIE\HiddenString\HiddenString;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

// Lade Umgebungsvariablen aus der .env-Datei
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Überprüfen, ob die .env-Datei korrekt geladen wurde
if (empty($_ENV['ENCRYPTION_KEY'])) {
    die("Fehler: Verschlüsselungsschlüssel nicht in der Umgebungsdatei gesetzt.");
}

// Monolog Logger einrichten
$log = new Logger('user_registration');
$log->pushHandler(new StreamHandler(__DIR__ . '/registrierung.log', Level::Warning)); // In Produktion auf Warning oder Info setzen

$response = [];
$errors = [];

// Verbindung zur DB
$link = null;
try {
    $link = DbFunctions::connectWithDatabase();
    if (!$link) {
        $log->error('Fehler bei der Verbindung zur Datenbank');
        die('Fehler bei der Verbindung zur Datenbank');
    }

    // Eingaben escapen und HTML-Sonderzeichen codieren
    $username = htmlspecialchars(DbFunctions::escape($link, $_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(DbFunctions::escape($link, $_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? ''; // Passwort-Bestätigungsfeld

    // Serverseitige Validierung
    if (empty($username)) {
        $errors['username'] = 'Bitte geben Sie einen Benutzernamen ein.';
        $log->warning('Benutzername fehlt bei der Registrierung');
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors['username'] = 'Benutzername darf nur alphanumerische Zeichen und Unterstriche enthalten und muss zwischen 3 und 20 Zeichen lang sein.';
        $log->warning('Ungültiger Benutzername', ['username' => $username]);
    }

    if (empty($email)) {
        $errors['email'] = 'Bitte geben Sie eine E-Mail-Adresse ein.';
        $log->warning('E-Mail-Adresse fehlt bei der Registrierung');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein (max. 255 Zeichen).';
        $log->warning('Ungültige oder zu lange E-Mail-Adresse', ['email' => $email]);
    }

    if (empty($password)) {
        $errors['password'][] = 'Bitte geben Sie ein Passwort ein.';
        $log->warning('Passwort fehlt bei der Registrierung');
    } elseif (strlen($password) < 8 || strlen($password) > 128) { // Maximale Länge hinzugefügt
        $errors['password'][] = 'Passwort muss zwischen 8 und 128 Zeichen lang sein.';
        $log->warning('Passwort zu kurz oder zu lang bei der Registrierung', ['password_length' => strlen($password)]);
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'][] = 'Passwort muss mindestens einen Großbuchstaben enthalten.';
        $log->warning('Passwort ohne Großbuchstaben', ['password' => $password]);
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'][] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten.';
        $log->warning('Passwort ohne Kleinbuchstaben', ['password' => $password]);
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'][] = 'Passwort muss mindestens eine Zahl enthalten.';
        $log->warning('Passwort ohne Zahl', ['password' => $password]);
    } elseif (!preg_match('/[\W_]/', $password)) {
        $errors['password'][] = 'Passwort muss mindestens ein Sonderzeichen enthalten.';
        $log->warning('Passwort ohne Sonderzeichen', ['password' => $password]);
    }

    // Passwort-Bestätigung überprüfen
    if (empty($passwordConfirm)) {
        $errors['password_confirm'] = 'Bitte bestätigen Sie Ihr Passwort.';
        $log->warning('Passwort-Bestätigung fehlt');
    } elseif ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Die Passwörter stimmen nicht überein.';
        $log->warning('Passwörter stimmen nicht überein');
    }

    if (empty($errors)) {
        // Prüfen ob Benutzername oder E-Mail bereits existieren
        $queryUsername = "SELECT COUNT(*) FROM users WHERE username = ?";
        $queryEmail = "SELECT COUNT(*) FROM users WHERE email = ?";

        $stmt = DbFunctions::executePreparedQuery($link, $queryUsername, [$username]);
        if ($stmt) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_row($result);
            if ($row[0] > 0) {
                $errors['username'] = 'Benutzername ist bereits vergeben.';
                $log->warning('Benutzername bereits vergeben', ['username' => $username]);
            }
            mysqli_stmt_close($stmt); // Statement schließen
        }

        $stmt = DbFunctions::executePreparedQuery($link, $queryEmail, [$email]);
        if ($stmt) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_row($result);
            if ($row[0] > 0) {
                $errors['email'] = 'E-Mail-Adresse ist bereits registriert.';
                $log->warning('E-Mail-Adresse bereits registriert', ['email' => $email]);
            }
            mysqli_stmt_close($stmt); // Statement schließen
        }
    }

    if (empty($errors)) {
        try {
            // Verschlüsselungsschlüssel aus der Umgebungsvariable laden
            $keyString = getenv('ENCRYPTION_KEY');

            if (empty($keyString)) {
                throw new Exception('Verschlüsselungsschlüssel nicht gefunden. Bitte stellen Sie sicher, dass der Schlüssel in der .env-Datei gesetzt ist.');
            }

            // Überprüfen, ob der Schlüssel ein gültiger Wert ist
            if (empty($keyString) || !is_string($keyString)) {
                throw new Exception('Der Verschlüsselungsschlüssel ist ungültig oder leer.');
            }

            // Schlüssel in ein HiddenString-Objekt umwandeln
            $hiddenKey = new HiddenString($keyString);

            // Schlüssel in ein EncryptionKey-Objekt umwandeln
            $key = KeyFactory::importEncryptionKey($hiddenKey);

            // Passwort verschlüsseln mit Halite
            $encryptedPassword = Password::hash($password, $key);

            // In DB einfügen (mit Prepared Statement)
            $query = "INSERT INTO users (username, email, passwort) VALUES (?, ?, ?)";
            $stmt = DbFunctions::executePreparedQuery($link, $query, [$username, $email, $encryptedPassword]);

            if ($stmt) {
                $response['success'] = true;
                $log->info('Benutzer erfolgreich registriert', ['username' => $username, 'email' => $email]);

                // Hier könnte die Logik für die E-Mail-Verifizierung eingefügt werden
                // z.B. Generieren eines Verifizierungstokens, Speichern in der DB und Senden einer E-Mail
                // $verificationToken = bin2hex(random_bytes(32));
                // $queryToken = "UPDATE users SET verification_token = ? WHERE id = ?";
                // DbFunctions::executePreparedQuery($link, $queryToken, [$verificationToken, mysqli_insert_id($link)]);
                // sendVerificationEmail($email, $verificationToken);

            } else {
                $response = [
                    'success' => false,
                    'message' => 'Fehler beim Einfügen in die Datenbank.',
                ];
                $log->error('Fehler beim Einfügen in die Datenbank', ['username' => $username, 'email' => $email, 'error' => mysqli_error($link)]);
            }
            mysqli_stmt_close($stmt); // Statement schließen
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Fehler bei der Passwortverschlüsselung oder Datenbankoperation: ' . $e->getMessage(),
            ];
            $log->error('Fehler bei der Passwortverschlüsselung oder DB-Operation', ['error_message' => $e->getMessage()]);
        }
    } else {
        $response = [
            'success' => false,
            'errors' => $errors,
        ];
    }
} finally {
    if ($link) {
        mysqli_close($link); // Verbindung sicher schließen
    }
}

header('Content-Type: application/json');
echo json_encode($response);

// Funktion zum Senden der Verifizierungs-E-Mail (muss implementiert werden)
// function sendVerificationEmail($email, $token) {
//     $subject = 'Bitte bestätigen Sie Ihre E-Mail-Adresse';
//     $message = 'Klicken Sie auf den folgenden Link, um Ihre E-Mail-Adresse zu bestätigen: ...';
//     // Verwenden Sie eine geeignete E-Mail-Versandbibliothek oder Funktion
//     mail($email, $subject, $message);
// }
?>


