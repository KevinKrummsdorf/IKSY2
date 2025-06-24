<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';

$response = ['success' => false];

$pdo = DbFunctions::db_connect();

try {

    // reCAPTCHA prüfen
    $token  = $_POST['recaptcha_token'] ?? '';
    $secret = $config['recaptcha']['secret_key'];
    if (!recaptcha_verify($pdo, $token, $secret, $config['recaptcha']['min_score'])) {
        $response['errors']['recaptcha'] = 'reCAPTCHA fehlgeschlagen.';
        throw new DomainException('reCAPTCHA ungültig.');
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
        throw new DomainException('Benutzer existiert bereits.');
    }

    // Passwort hashen (jetzt nativ ohne Halite)
    $hash = password_hash($pw, PASSWORD_DEFAULT);

    DbFunctions::beginTransaction();

    $userId = DbFunctions::insertUser($username, $email, $hash);

    DbFunctions::assignRole($userId, 3);

    DbFunctions::commit();

    try {
        require_once __DIR__ . '/../includes/verification.inc.php';
        sendVerificationEmail($pdo, $userId, $username, $email);
        $response['message'] = 'Bestätigungs-E-Mail gesendet.';
    } catch (Exception $e) {

    }

    $response['success'] = true;

} catch (DomainException $e) {
    if (!isset($response['errors'])) {
        $response['message'] = $e->getMessage();
    }
    DbFunctions::rollBack();

} catch (Throwable $e) {
    $response['message'] = (defined('DEBUG'))
        ? $e->getMessage()
        : 'Interner Serverfehler. Bitte später erneut versuchen.';
    DbFunctions::rollBack();
}

echo json_encode($response);
exit;
