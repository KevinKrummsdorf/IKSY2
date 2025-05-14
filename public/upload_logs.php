<?php
declare(strict_types=1);

// Zentrale Initialisierung
session_start();
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// Nur Admins/Mods dürfen
if (! $isAdmin && ! $isMod) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Zugriff verweigert.';
    exit;
}

// DB-Verbindung
$pdo = DbFunctions::db_connect();

// Pagination-Parameter
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

// Gesamtanzahl der Upload-Logs
$totalCount = countUploadLogs($pdo);
$totalPages = (int)ceil($totalCount / $pageSize);

// Logs abrufen für die aktuelle Seite
$uploadLogs = getUploadLogsPage($pdo, $pageSize, $offset);

// Smarty-Variablen zuweisen
$smarty->assign('upload_logs',  $uploadLogs);
$smarty->assign('isAdmin',      $isAdmin);
$smarty->assign('isMod',        $isMod);
$smarty->assign('username',     $_SESSION['username'] ?? '');
$smarty->assign('currentPage',  $currentPage);
$smarty->assign('totalPages',   $totalPages);

// Template anzeigen
$smarty->display('upload_logs.tpl');
