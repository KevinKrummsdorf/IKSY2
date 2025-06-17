<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Optional: nur für eingeloggte Benutzer
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Zugriff verweigert – bitte einloggen.');
}

// Parameter prüfen
$uploadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($uploadId <= 0) {
    http_response_code(400);
    exit('Ungültige ID.');
}

// Upload-Daten abrufen
$upload = DbFunctions::getApprovedUploadById($uploadId);

if (!$upload) {
    http_response_code(404);
    exit('Upload nicht gefunden oder nicht freigegeben.');
}

if ($upload['group_id'] !== null) {
    $role = DbFunctions::fetchUserRoleInGroup((int)$upload['group_id'], (int)$_SESSION['user_id']);
    if ($role === null) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
}

$basePath = realpath(__DIR__ . '/../uploads');
if ($basePath === false) {
    http_response_code(500);
    exit('Upload-Verzeichnis fehlt.');
}
$filePath = realpath($basePath . '/' . $upload['stored_name']);

if ($filePath === false || strpos($filePath, $basePath) !== 0 || !is_file($filePath)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

// Content-Type und Download-Header setzen
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($filePath);
$filename = basename($upload['stored_name']);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Expires: 0');

readfile($filePath);
exit;
