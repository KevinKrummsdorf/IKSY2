<?php
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Sicherheitscheck – nur Dateinamen zulassen
$filename = basename($_GET['file'] ?? '');

$upload = DbFunctions::getApprovedUploadByStoredName($filename);
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

$path = __DIR__ . '/../uploads/' . $filename;

if (!preg_match('/\.pdf$/i', $filename) || !file_exists($path)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
