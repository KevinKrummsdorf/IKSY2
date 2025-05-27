<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    http_response_code(403);
    exit('Zugriff verweigert.');
}

$filters = [
    'success'    => trim($_GET['success'] ?? ''),
    'action'     => trim($_GET['action'] ?? ''),
    'hostname'   => trim($_GET['hostname'] ?? ''),
    'score_min'  => trim($_GET['score_min'] ?? ''),
    'score_max'  => trim($_GET['score_max'] ?? ''),
    'from_date'  => trim($_GET['from_date'] ?? ''),
    'to_date'    => trim($_GET['to_date'] ?? ''),
];

$doExport = isset($_GET['export']) && $_GET['export'] === 'csv';

$pdo = DbFunctions::db_connect();

// CSV-Export
if ($doExport) {
    $logs = DbFunctions::getFilteredCaptchaLogs($filters, null, null, true); // ← mit token

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=captcha_logs_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Token', 'Erfolg', 'Score', 'Aktion', 'Hostname', 'Fehler', 'Zeitpunkt']);
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['token'],
            $log['success'] ? '✔' : '✘',
            $log['score'],
            $log['action'],
            $log['hostname'],
            $log['error_reason'] ?? '',
            $log['created_at'],
        ]);
    }
    fclose($output);
    exit;
}


// Pagination
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize = 25;
$offset = ($currentPage - 1) * $pageSize;

$totalCount = DbFunctions::countFilteredCaptchaLogs($filters);
$totalPages = (int)ceil($totalCount / $pageSize);
$logs = DbFunctions::getFilteredCaptchaLogs($filters, $pageSize, $offset);

$smarty->assign([
    'captcha_logs' => $logs,
    'currentPage'  => $currentPage,
    'totalPages'   => $totalPages,
    'filters'      => $filters,
    'username'     => $_SESSION['username'] ?? '',
    'isAdmin'      => $_SESSION['role'] === 'admin',
    'isMod'        => $_SESSION['role'] === 'mod',
]);

$smarty->display('captcha_logs.tpl');
