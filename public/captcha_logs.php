<?php
declare(strict_types=1);

// Zentrale Initialisierung & Session
require_once __DIR__ . '/../includes/config.inc.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Admin-Prüfung
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
if (! $isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    exit('Zugriff verweigert.');
}

// DB-Verbindung
$pdo = DbFunctions::db_connect();

// Pagination-Parameter
$currentPage = isset($_GET['page']) && is_numeric($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;
$perPage = 25;
$offset  = ($currentPage - 1) * $perPage;

// Gesamtanzahl und Gesamtseiten
$totalCount = countCaptchaLogs($pdo);
$totalPages = (int)ceil($totalCount / $perPage);

// Logs der aktuellen Seite laden
$logs = getCaptchaLogsPage($pdo, $perPage, $offset);

// Daten an Smarty übergeben
$smarty->assign('captcha_logs', $logs);
$smarty->assign('currentPage',  $currentPage);
$smarty->assign('totalPages',   $totalPages);
$smarty->assign('isAdmin',      $isAdmin);
$smarty->assign('username',     $_SESSION['username'] ?? '');

// Template anzeigen
$smarty->display('captcha_logs.tpl');
