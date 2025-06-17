<?php
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Relativen Pfad ermitteln und auf Gültigkeit prüfen
$relative = ltrim((string)($_GET['file'] ?? ''), '/');
$basePath = realpath(__DIR__ . '/../uploads');
if ($basePath === false) {
    http_response_code(500);
    exit('Upload-Verzeichnis fehlt.');
}

$filePath = realpath($basePath . '/' . $relative);
if ($filePath === false || strpos($filePath, $basePath) !== 0) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$filename = basename($filePath);

$upload = DbFunctions::getApprovedUploadByStoredName($relative);
if (!$upload) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

if ($upload['group_id'] !== null) {
    if (empty($_SESSION['user_id'])) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
    $role = DbFunctions::fetchUserRoleInGroup((int)$upload['group_id'], (int)$_SESSION['user_id']);
    if ($role === null) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
}

$path = $filePath;

if (!preg_match('/\.pdf$/i', $filename) || !is_file($path)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
