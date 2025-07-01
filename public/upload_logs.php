<?php
declare(strict_types=1);
// Session wird bereits in config.inc.php gestartet
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err) {
        echo "<pre>FATAL ERROR:\n" . print_r($err, true) . "</pre>";
    }
});

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}
if (!in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    $reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
    handle_error(403, $reason, 'both');
}

$role = $_SESSION['role'] ?? '';
$filters = [
    'user_id'     => trim($_GET['user_id'] ?? ''),
    'filename'    => trim($_GET['filename'] ?? ''),
    'from_date'   => trim($_GET['from_date'] ?? ''),
    'to_date'     => trim($_GET['to_date'] ?? ''),
    'course_name' => trim($_GET['course_name'] ?? ''),
];

$doExport = isset($_GET['export']) && $_GET['export'] === 'csv';

if ($doExport) {
    $logs = DbFunctions::getExtendedUploadLogs($filters);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=upload_logs_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Username', 'Dateiname', 'Kurs', 'Zeitpunkt']);

    foreach ($logs as $log) {
        fputcsv($output, [
            $log['user_id'],
            $log['username'] ?? '',
            $log['stored_name'],
            $log['course_name'] ?? '',
            $log['created_at'],
        ]);
    }

    fclose($output);
    exit;
}

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

$totalCount  = DbFunctions::countExtendedUploadLogs($filters);
$totalPages  = (int)ceil($totalCount / $pageSize);
$uploadLogs  = DbFunctions::getExtendedUploadLogs($filters, $pageSize, $offset);

$smarty->assign([
    'upload_logs'  => $uploadLogs,
    'currentPage'  => $currentPage,
    'totalPages'   => $totalPages,
    'filters'      => $filters,
    'username'     => $_SESSION['username'] ?? '',
    'isAdmin'      => $role === 'admin',
    'isMod'        => $role === 'mod',
]);

$smarty->display('upload_logs.tpl');
