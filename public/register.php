<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');


require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/csrf.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UserRepository.php';

$response = ['success' => false];

$db = new Database();
$userRepository = new UserRepository($db);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        validate_csrf_token();
    }
    // Eingaben validieren
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pw       = $_POST['password'] ?? '';
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
        if (isset($errors['password'])) {
            http_response_code(400);
            echo json_encode(['error' => ['code' => 'PASSWORD_WEAK']]);
            exit;
        }
        $response['errors'] = $errors;
        http_response_code(400);
        throw new DomainException('Ungültige Eingaben.');
    }

    // Doppelte prüfen
    if ($userRepository->usernameExists($username)) {
        http_response_code(409);
        echo json_encode(['error' => ['code' => 'USERNAME_EXISTS']]);
        exit;
    }
    if ($userRepository->emailExists($email)) {
        http_response_code(409);
        echo json_encode(['error' => ['code' => 'EMAIL_EXISTS']]);
        exit;
    }

    // Passwort hashen und Benutzer anlegen
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $userId = $userRepository->insertUser($username, $email, $hash);
    $userRepository->assignRole($userId, 3);

    try {
        require_once __DIR__ . '/../includes/verification.inc.php';
        sendVerificationEmail($db, $userId, $username, $email);
        $response['message'] = 'Bestätigungs-E-Mail gesendet.';
    } catch (Exception $e) {
        // Optional: Logging
    }

    $response['success'] = true;

} catch (DomainException $e) {
    if (!isset($response['errors'])) {
        $response['message'] = $e->getMessage();
    }
} catch (Throwable $e) {
    $response['message'] = (defined('DEBUG'))
        ? $e->getMessage()
        : 'Interner Serverfehler. Bitte später erneut versuchen.';
}

echo json_encode($response);
exit;
