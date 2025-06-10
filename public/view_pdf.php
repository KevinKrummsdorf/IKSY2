<?php
// Sicherheitscheck – nur Dateinamen zulassen
$filename = basename($_GET['file'] ?? '');

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
