<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriff nur für Admin/Mod
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    http_response_code(403);
    exit('Zugriff verweigert.');
}

$role = $_SESSION['role'] ?? '';
$filters = [
    'user_id'     => trim($_GET['user_id'] ?? ''),
    'ip_address'  => trim($_GET['ip_address'] ?? ''),
    'from_date'   => trim($_GET['from_date'] ?? ''),
    'to_date'     => trim($_GET['to_date'] ?? ''),
    'success'     => trim($_GET['success'] ?? ''),
];

$doExport = isset($_GET['export']) && $_GET['export'] === 'csv';

if ($doExport) {
    $logs = DbFunctions::getFilteredLoginLogs($filters);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=login_logs_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Benutzername', 'IP-Adresse', 'Status', 'Zeitpunkt', 'Grund']);

    foreach ($logs as $log) {
        fputcsv($output, [
            $log['user_id'] ?? '',
            $log['username'] ?? 'Unbekannt',
            $log['ip_address'],
            $log['success'] ? 'Erfolg' : 'Fehlgeschlagen',
            $log['created_at'],
            $log['reason'] ?? '',
        ]);
    }

    fclose($output);
    exit;
}

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

$totalCount  = DbFunctions::countFilteredLoginLogs($filters);
$totalPages  = (int)ceil($totalCount / $pageSize);
$loginLogs   = DbFunctions::getFilteredLoginLogs($filters, $pageSize, $offset);

if (!isset($smarty)) {
    echo "FEHLER: \$smarty nicht initialisiert!";
    exit;
}


$smarty->assign([
    'login_logs'   => $loginLogs,
    'currentPage'  => $currentPage,
    'totalPages'   => $totalPages,
    'filters'      => $filters,
    'username'     => $_SESSION['username'] ?? '',
    'isAdmin'      => $role === 'admin',
    'isMod'        => $role === 'mod',
]);

$smarty->display('login_logs.tpl');

