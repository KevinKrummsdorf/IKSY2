<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';

// Optional: nur für eingeloggte Benutzer
if (empty($_SESSION['user_id'])) {
    $reason = 'Zugriff verweigert – bitte einloggen.';
    handle_error(401, $reason, 'both');
}

// Parameter prüfen
$uploadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($uploadId <= 0) {
    $reason = 'Ungültige ID.';
    handle_error(400, $reason);
}

$db = new Database();
$uploadRepository = new UploadRepository($db);

// Upload-Daten abrufen
$upload = $uploadRepository->getApprovedUploadById($uploadId);

if (!$upload) {
    $reason = 'Upload nicht gefunden oder nicht freigegeben.';
    handle_error(404, $reason);
}

$basePath = realpath(__DIR__ . '/../uploads/');
$filePath = $basePath . DIRECTORY_SEPARATOR . $upload['stored_name'];

if (!is_file($filePath)) {
    $reason = 'Datei nicht gefunden.';
    handle_error(404, $reason);
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
