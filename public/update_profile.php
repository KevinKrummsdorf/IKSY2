<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt.');
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

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
            break;

        case 'update_personal':
            $fields = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name'  => trim($_POST['last_name'] ?? ''),
                'about_me'   => trim($_POST['about_me'] ?? '')
            ];
            DbFunctions::updateUserProfile($userId, $fields);
            break;

        case 'update_socials':
            $fields = [
                'instagram' => trim($_POST['instagram'] ?? ''),
                'discord'   => trim($_POST['discord'] ?? ''),
                'ms_teams'  => trim($_POST['ms_teams'] ?? '')
            ];
            DbFunctions::updateUserProfile($userId, $fields);
            break;

        default:
            throw new RuntimeException('Ungültige Aktion.');
    }

    header('Location: profile.php');
    exit;
} catch (Throwable $e) {
    http_response_code(400);
    echo $e->getMessage();
}

