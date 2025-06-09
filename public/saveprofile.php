<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/db.inc.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt.');
}

$userId = $_SESSION['user_id'];

// POST-Daten holen und vorbereiten
$data = [];
$keys = ['first_name', 'last_name', 'birthdate', 'location', 'about_me', 'instagram', 'tiktok', 'discord', 'ms_teams'];

foreach ($keys as $key) {
    $value = $_POST[$key] ?? null;
    
    if ($key === 'birthdate' && trim($value) === '') {
        $data[$key] = null;
    } else {
        $data[$key] = trim($value);
    }
}

// Optional: Profilbild-Upload verarbeiten
if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['profile_picture']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $mimeType = mime_content_type($tmpName);
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mimeType, $allowedTypes)) {
        exit('❌ Ungültiger Bildtyp.');
    }
    
    // Zielverzeichnis und Dateiname
    $uploadDir = __DIR__ . '/../uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }
    
    $fileName = uniqid('profile_', true) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($tmpName, $targetPath)) {
        $data['profile_picture'] = $fileName;
    } else {
        exit('❌ Fehler beim Hochladen des Bildes.');
    }
}

// Speichern in DB
DbFunctions::updateUserProfile($userId, $data);

// Weiterleitung
header('Location: /iksy05/StudyHub/public/profile.php?success=1');
exit;
