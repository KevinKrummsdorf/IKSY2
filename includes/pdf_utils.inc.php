<?php
declare(strict_types=1);

//use TCPDF;

/**
 * Konvertiert eine Datei mit TCPDF nach PDF.
 * Unterstützt einfache Konvertierung von Bildern, Textdateien,
 * DOCX und PPTX (reiner Text).
 */
function convert_file_to_pdf(string $sourcePath, string $destPath): bool
{
    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
        return copy($sourcePath, $destPath);
    }

    $pdf = new TCPDF();
    $pdf->SetCreator('StudyHub');
    $pdf->AddPage();

    switch ($ext) {
        case 'txt':
            $text = @file_get_contents($sourcePath) ?: '';
            $pdf->Write(0, $text);
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
            $pdf->Image($sourcePath, 15, 40, 170, 0, '', '', '', true);
            break;
        case 'docx':
            $text = extract_text_from_docx($sourcePath);
            $pdf->Write(0, $text);
            break;
        case 'pptx':
            $text = extract_text_from_pptx($sourcePath);
            $pdf->Write(0, $text);
            break;
        default:
            // Unsupported type: nur Dateiname einbetten
            $pdf->Write(0, 'Original file: ' . basename($sourcePath));
    }

    $pdf->Output($destPath, 'F');
    return true;
}

/**
 * Extrahiert einfachen Text aus einer DOCX-Datei.
 */
function extract_text_from_docx(string $file): string
{
    $zip = new ZipArchive();
    if ($zip->open($file) === true) {
        $data = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($data !== false) {
            return strip_tags($data);
        }
    }
    return '';
}

/**
 * Extrahiert Text aus allen Folien einer PPTX-Datei.
 */
function extract_text_from_pptx(string $file): string
{
    $zip = new ZipArchive();
    $text = '';
    if ($zip->open($file) === true) {
        for ($i = 1; $zip->locateName("ppt/slides/slide{$i}.xml") !== false; $i++) {
            $data = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if ($data !== false) {
                $text .= strip_tags($data) . "\n";
            }
        }
        $zip->close();
    }
    return $text;
}
