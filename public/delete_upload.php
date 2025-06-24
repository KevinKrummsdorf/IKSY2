<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Zugriff verweigert.');
}

if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
    http_response_code(403);
    exit('Ungültiger CSRF-Token.');
}

$uploadId = (int)($_POST['upload_id'] ?? 0);
if ($uploadId <= 0) {
    http_response_code(400);
    exit('Ungültige ID.');
}

$userId = (int)$_SESSION['user_id'];

try {
    $storedName = DbFunctions::deleteUpload($uploadId, $userId);
    if ($storedName === null) {
        http_response_code(403);
        exit('Upload nicht gefunden oder keine Berechtigung.');
    }

    $file = __DIR__ . '/../uploads/' . $storedName;
    if (is_file($file)) {
        unlink($file);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Upload wurde gelöscht.'];
} catch (Exception $e) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Fehler beim Löschen des Uploads.'];
}

header('Location: dashboard.php');
exit;
