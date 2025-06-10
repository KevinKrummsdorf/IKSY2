<?php
declare(strict_types=1);

/**
 * Konvertiert eine Datei mit LibreOffice in ein PDF.
 * Gibt den Pfad zur PDF-Datei oder null bei Fehler zurück.
 */
function convertToPdf(string $filePath): ?string
{
    $log = LoggerFactory::get('converter');

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
        return $filePath; // bereits PDF
    }

    $outputDir   = dirname($filePath);
    $escapedInput = escapeshellarg($filePath);
    $escapedDir  = escapeshellarg($outputDir);

    $binary = trim(shell_exec('command -v libreoffice')); // libreoffice path oder leer
    if ($binary === '') {
        $binary = trim(shell_exec('command -v soffice'));
    }
    if ($binary === '') {
        $log->warning('LibreOffice nicht gefunden', ['file' => $filePath]);
        return null; // LibreOffice nicht installiert
    }

    $cmd = "$binary --headless --convert-to pdf --outdir $escapedDir $escapedInput";
    exec($cmd . ' 2>&1', $out, $ret);

    if ($ret === 0) {
        $pdfPath = $outputDir . '/' . basename($filePath, '.' . $ext) . '.pdf';
        if (file_exists($pdfPath)) {
            return $pdfPath;
        }
        $log->error('PDF nach erfolgreichem Lauf nicht gefunden', ['file' => $filePath, 'cmd' => $cmd]);
    } else {
        $log->error('LibreOffice-Konvertierung fehlgeschlagen', [
            'cmd' => $cmd,
            'output' => implode("\n", $out),
            'code' => $ret,
        ]);
    }
    return null;
}

/**
 * Konvertiert hochgeladene Dateien (außer PPT/PPTX) automatisch nach PDF.
 * Gibt den neuen Dateinamen zurück, wenn konvertiert wurde.
 */
function handleUploadConversion(string $filePath, bool $force = false): string
{
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // PPT und PPTX nur auf Wunsch konvertieren
    if (!$force && in_array($ext, ['ppt', 'pptx'], true)) {
        return basename($filePath);
    }

    $pdf = convertToPdf($filePath);
    if ($pdf) {
        // Original entfernen und neuen Namen zurückgeben
        if ($pdf !== $filePath) {
            @unlink($filePath);
        }
        return basename($pdf);
    }

    return basename($filePath);
}
