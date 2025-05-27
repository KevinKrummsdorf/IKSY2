<?php
$filename = basename($_GET['file'] ?? '');
$path = realpath(__DIR__ . '/../uploads/' . $filename);

if (!$filename || !$path || !file_exists($path)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

header('Content-Type: text/plain');
readfile($path);
exit;
