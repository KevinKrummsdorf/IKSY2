<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt.');
}

$userId = $_SESSION['user_id'];
$currentUser = DbFunctions::fetchUserById($userId);
$currentEmail = $currentUser['email'] ?? '';

// POST-Daten holen und vorbereiten
$data = [];
$keys = ['first_name', 'last_name', 'birthdate', 'location', 'about_me'];

foreach ($keys as $key) {
    $value = $_POST[$key] ?? null;

    if ($key === 'birthdate' && trim($value) === '') {
        $data[$key] = null;
    } else {
        $data[$key] = trim($value);
    }
}

if (!empty($data['birthdate'])) {
    try {
        $birthObj = new DateTime($data['birthdate']);
        $minDate  = new DateTime('-16 years');

        if ($birthObj > $minDate) {
            exit('Du musst mindestens 16 Jahre alt sein.');
        }

        $data['birthdate'] = $birthObj->format('Y-m-d');
    } catch (Throwable $e) {
        exit('Ungültiges Geburtsdatum.');
    }
}

$newEmail = trim($_POST['email'] ?? '');
if ($newEmail !== '' && $newEmail !== $currentEmail) {
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        exit('Ungültige E-Mail-Adresse.');
    }
    $count = DbFunctions::countWhere('users', 'email', $newEmail);
    if ($count > 0) {
        exit('E-Mail-Adresse wird bereits verwendet.');
    }
    DbFunctions::updateEmail($userId, $newEmail);
    DbFunctions::unverifyUser($userId);
    require_once __DIR__ . '/../includes/verification.inc.php';
    sendVerificationEmail(DbFunctions::db_connect(), $userId, $currentUser['username'], $newEmail);
}

// Optional: Profilbild-Upload verarbeiten
if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
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

    // Einige PHP-Konfigurationen melden PNGs als image/x-png
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-png'];
    if ($mimeType && !in_array($mimeType, $allowedTypes, true)) {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Ungültiger Bildtyp.'
        ];
        header('Location: edit_profile');
        exit;
    }
    
    // Zielverzeichnis und Dateiname
    $uploadDir = __DIR__ . '/../uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }
    
    $fileName = uniqid('profile_', true) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($tmpName, $targetPath)) {
        // Fallback falls PHP das Tmpfile nicht als Upload erkennt
        if (!rename($tmpName, $targetPath)) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Fehler beim Hochladen des Bildes.'
            ];
            header('Location: edit_profile');
            exit;
        }
    }

    $data['profile_picture'] = $fileName;
}

// Speichern in DB
DbFunctions::updateUserProfile($userId, $data);

// Social-Media-Handles speichern
$platforms = ['instagram', 'tiktok', 'discord', 'ms_teams', 'twitter', 'linkedin', 'github'];
foreach ($platforms as $platform) {
    $handle = htmlspecialchars(trim($_POST[$platform] ?? ''), ENT_QUOTES, 'UTF-8');
    DbFunctions::saveUserSocialMedia($userId, $platform, $handle);
}

// Weiterleitung
header('Location: ' . build_url('profile/my', ['success' => 1]));
exit;
