<?php
/**
 * Liefert Dateien aus dem Upload-Verzeichnis aus.
 * Unterverzeichnisse wie "profile_pictures" werden unterstützt, sofern
 * der Pfad innerhalb von ../uploads/ liegt.
 */

$relativePath = $_GET['file'] ?? '';

// Basisverzeichnis bestimmen
$basePath = realpath(__DIR__ . '/../uploads');
if ($basePath === false) {
    http_response_code(500);
    exit('Upload-Verzeichnis fehlt.');
}

// Zielpfad auflösen und sicherstellen, dass er im Upload-Verzeichnis liegt
$targetPath = realpath($basePath . '/' . $relativePath);
if (
    !$relativePath ||
    $targetPath === false ||
    strpos($targetPath, $basePath) !== 0 ||
    !is_file($targetPath)
) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
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
