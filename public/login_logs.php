<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';


// Session starten, falls noch nicht aktiv
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Login-Schutz
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Admin-Prüfung
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
if (! $isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Zugriff verweigert.';
    exit;
}

// DB-Verbindung
$pdo = DbFunctions::db_connect();

// Pagination-Parameter
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage     = 25;
$offset      = ($currentPage - 1) * $perPage;

// Gesamtanzahl der Login-Logs
$totalCount = countLoginLogs($pdo);
$totalPages = (int)ceil($totalCount / $perPage);

// Logs der aktuellen Seite laden
$loginLogs = getLoginLogsPage($pdo, $perPage, $offset, $isAdmin);

// Smarty-Variablen zuweisen
$smarty->assign('login_logs',  $loginLogs);
$smarty->assign('currentPage', $currentPage);
$smarty->assign('totalPages',  $totalPages);

// Template anzeigen
$smarty->display('login_logs.tpl');
