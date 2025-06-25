<?php
// Sicherheitscheck – nur Dateinamen zulassen
$filename = basename($_GET['file'] ?? '');

$path = __DIR__ . '/../uploads/' . $filename;

if (!preg_match('/\.pdf$/i', $filename) || !file_exists($path)) {
    $reason = 'Datei nicht gefunden.';
    handle_error(404, $reason);
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
