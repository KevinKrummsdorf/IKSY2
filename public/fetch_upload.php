<?php

require_once __DIR__ . '/../includes/config.inc.php';

$relativePath = $_GET['file'] ?? '';

// Basisverzeichnis bestimmen
$basePath = realpath(__DIR__ . '/../uploads');
if ($basePath === false) {
    $reason = 'Upload-Verzeichnis fehlt.';
    handle_error(500, $reason);
}

// Zielpfad auflösen und sicherstellen, dass er im Upload-Verzeichnis liegt
$targetPath = realpath($basePath . '/' . $relativePath);
if (
    !$relativePath ||
    $targetPath === false ||
    strpos($targetPath, $basePath) !== 0 ||
    !is_file($targetPath)
) {
    $reason = 'Datei nicht gefunden.';
    handle_error(404, $reason);
}

// Content-Type anhand von MIME ermitteln
$mimeType = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $targetPath) ?: $mimeType;
    finfo_close($finfo);
} elseif (function_exists('mime_content_type')) {
    $mimeType = mime_content_type($targetPath) ?: $mimeType;
}

header('Content-Type: ' . $mimeType);
readfile($targetPath);
