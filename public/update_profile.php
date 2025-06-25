<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$_SESSION['flash'] = null; // clear previous flash

try {
    switch ($action) {
        case 'update_username':
            $username = trim($_POST['username'] ?? '');
            if ($username === '') {
                throw new RuntimeException('Ungültiger Benutzername.');
            }
            if (DbFunctions::countWhere('users', 'username', $username) > 0) {
                throw new RuntimeException('Benutzername bereits vergeben.');
            }
            DbFunctions::execute('UPDATE users SET username = :u WHERE id = :id', [
                ':u' => $username,
                ':id' => $userId
            ], false);
            $_SESSION['username'] = $username;
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Benutzername aktualisiert.'];
            break;

        case 'update_email':
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Ungültige E-Mail-Adresse.');
            }
            if (DbFunctions::countWhere('users', 'email', $email) > 0) {
                throw new RuntimeException('E-Mail-Adresse wird bereits verwendet.');
            }
            DbFunctions::updateEmail($userId, $email);
            DbFunctions::unverifyUser($userId);
            require_once __DIR__ . '/../includes/verification.inc.php';
            $user = DbFunctions::fetchUserById($userId);
            sendVerificationEmail(DbFunctions::db_connect(), $userId, $user['username'], $email);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'E-Mail-Adresse aktualisiert. Bitte bestätige sie erneut.'];
            break;

        case 'update_password':
            $old     = $_POST['old_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['new_password_confirm'] ?? '';
            if ($old === '' || $new === '' || $confirm === '') {
                throw new RuntimeException('Alle Felder müssen ausgefüllt werden.');
            }
            if ($new !== $confirm) {
                throw new RuntimeException('Passwörter stimmen nicht überein.');
            }
            $user = DbFunctions::fetchUserById($userId);
            if (!$user || !verifyPassword($old, $user['password_hash'])) {
                throw new RuntimeException('Aktuelles Passwort falsch.');
            }
            $hash = password_hash($new, PASSWORD_DEFAULT);
            DbFunctions::updatePassword($userId, $hash);

            // Confirmation email
            try {
                $subject = 'Passwort geändert';
                $html    = "<p>Hallo {$user['username']},</p><p>dein Passwort wurde erfolgreich geändert. " .
                           "Wenn du das nicht warst, kontaktiere bitte sofort den Support.</p>" .
                           '<p>Viele Grüße,<br>StudyHub-Team</p>';
                sendMail($user['email'], $user['username'], $subject, $html);
            } catch (Throwable $mailEx) {
                error_log('Passwort-Change-E-Mail fehlgeschlagen: ' . $mailEx->getMessage());
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Passwort erfolgreich geändert.'];
            break;

        case 'update_personal':
            $birthRaw   = trim($_POST['birthdate'] ?? '');
            $birthdate  = null;

            if ($birthRaw !== '') {
                try {
                    $birthObj = new DateTime($birthRaw);
                    $minDate  = new DateTime('-16 years');

                    if ($birthObj > $minDate) {
                        throw new RuntimeException('Du musst mindestens 16 Jahre alt sein.');
                    }

                    $birthdate = $birthObj->format('Y-m-d');
                } catch (Throwable $e) {
                    throw new RuntimeException('Ungültiges Geburtsdatum.');
                }
            }

            $fields = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name'  => trim($_POST['last_name'] ?? ''),
                'about_me'   => trim($_POST['about_me'] ?? ''),
                'birthdate'  => $birthdate,
            ];

            DbFunctions::updateUserProfile($userId, $fields);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Persönliche Daten aktualisiert.'];
            break;

        case 'update_socials':
            $platforms = ['instagram', 'tiktok', 'discord', 'ms_teams', 'twitter', 'linkedin', 'github'];
            foreach ($platforms as $platform) {
                $handle = htmlspecialchars(trim($_POST[$platform] ?? ''), ENT_QUOTES, 'UTF-8');
                DbFunctions::saveUserSocialMedia($userId, $platform, $handle);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Social-Media-Daten aktualisiert.'];
            break;

        case 'update_picture':
            if (empty($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Kein Bild hochgeladen.');
            }

            $tmpName  = $_FILES['profile_picture']['tmp_name'];
            $ext      = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

            $mimeType = '';
            if (function_exists('finfo_open')) {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tmpName) ?: '';
                finfo_close($finfo);
            } elseif (function_exists('mime_content_type')) {
                $mimeType = mime_content_type($tmpName);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-png'];
            if ($mimeType && !in_array($mimeType, $allowedTypes, true)) {
                throw new RuntimeException('Ungültiger Bildtyp.');
            }

            $uploadDir = __DIR__ . '/../uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileName   = uniqid('profile_', true) . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                if (!rename($tmpName, $targetPath)) {
                    throw new RuntimeException('Fehler beim Hochladen des Bildes.');
                }
            }

            DbFunctions::updateUserProfile($userId, ['profile_picture' => $fileName]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Profilbild aktualisiert.'];
            break;

        default:
            throw new RuntimeException('Ungültige Aktion.');
    }

    header('Location: ' . build_url('profile/my'));
    exit;
} catch (Throwable $e) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
    header('Location: ' . build_url('profile/my'));
    exit;
}

