<?php
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Angeforderten relativen Pfad ermitteln
$relative = ltrim((string)($_GET['file'] ?? ''), '/');

$upload = DbFunctions::getApprovedUploadByStoredName($relative);
if (!$upload) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$filePath = resolve_upload_path($upload['stored_name'], $upload['group_id']);
if ($filePath === null || !is_file($filePath)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$filename = basename($filePath);

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
