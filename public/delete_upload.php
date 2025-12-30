<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user_id'])) {
    $reason = 'Zugriff verweigert.';
    handle_error(401, $reason, 'both');
}

if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
    $reason = 'Ungültiger CSRF-Token.';
    handle_error(403, $reason, 'both');
}

$uploadId = (int)($_POST['upload_id'] ?? 0);
if ($uploadId <= 0) {
    $reason = 'Ungültige ID.';
    handle_error(400, $reason);
}

$userId = (int)$_SESSION['user_id'];

try {
    $db = new Database();
    $uploadRepository = new UploadRepository($db);
    $storedName = $uploadRepository->deleteUpload($uploadId, $userId);
    if ($storedName === null) {
        $reason = 'Upload nicht gefunden oder keine Berechtigung.';
        handle_error(403, $reason, 'both');
    }

    $file = __DIR__ . '/../uploads/' . $storedName;
    if (is_file($file)) {
        unlink($file);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Upload wurde gelöscht.'];
} catch (Exception $e) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Fehler beim Löschen des Uploads.'];
}

header('Location: my_uploads.php');
exit;
