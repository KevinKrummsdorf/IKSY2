<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Nur Dateinamen akzeptieren
$filename = basename($_GET['file'] ?? '');
$path     = __DIR__ . '/../uploads/' . $filename;

if (!preg_match('/\.pdf$/i', $filename) || !is_file($path)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$shouldWatermark = empty($_SESSION['user_id']);

if (!$shouldWatermark) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// PDF mit Wasserzeichen erzeugen (PDF/A-1b)
$pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false, true);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetCreator('StudyHub');
$pdf->SetAuthor('StudyHub');
$pdf->SetTitle($filename);
$pdf->SetSubject('Wasserzeichen');

$pageCount = $pdf->setSourceFile($path);
for ($page = 1; $page <= $pageCount; $page++) {
    $tpl = $pdf->importPage($page);
    $size = $pdf->getTemplateSize($tpl);
    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    $pdf->useTemplate($tpl);

    $pdf->SetFont('helvetica', 'B', 48);
    $pdf->SetTextColor(200, 200, 200);
    $pdf->SetXY(0, $size['height'] / 2 - 24);
    $pdf->Cell($size['width'], 48, 'KOPIE', 0, 0, 'C');
}

$pdf->SetProtection(['print']);
$pdf->Output($filename, 'I');
exit;
