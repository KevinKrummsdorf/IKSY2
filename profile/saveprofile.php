<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt.');
}

$userId = $_SESSION['user_id'];

// POST-Daten holen
$data = [];
$keys = ['first_name', 'last_name', 'birthdate', 'location', 'about_me', 'instagram', 'tiktok', 'discord', 'ms_teams'];

foreach ($keys as $key) {
    $value = $_POST[$key] ?? null;
    $data[$key] = ($key === 'birthdate' && trim($value) === '') ? null : trim($value);
}

// Profilbild-Upload
if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $tmpName  = $_FILES['profile_picture']['tmp_name'];
    $ext      = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    
    // MIME-Typ prüfen
    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName) ?: '';
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($tmpName);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-png'];
    if ($mimeType && !in_array($mimeType, $allowedTypes, true)) {
        exit('❌ Ungültiger Bildtyp.');
    }
    
    // Zielverzeichnis direkt in public/
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }
    
    // Dateiname (alternativ: uniqid(...) lassen)
    $fileName = 'user_' . $userId . '.' . $ext;
    $targetPath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($tmpName, $targetPath)) {
        if (!rename($tmpName, $targetPath)) {
            exit('❌ Fehler beim Hochladen des Bildes.');
        }
    }
    
    $data['profile_picture'] = $fileName;
}

// Speichern in DB
DbFunctions::updateUserProfile($userId, $data);

// Weiterleitung
header('Location: profile.php?success=1');
exit;
