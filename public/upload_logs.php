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

// Nur Admins oder Moderatoren dürfen zugreifen
if (! $isAdmin && ! $isMod) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Zugriff verweigert.';
    exit;
}

// Pagination-Parameter
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

// Upload-Log-Daten über DbFunctions
$totalCount  = DbFunctions::countUploadLogs();
$totalPages  = (int)ceil($totalCount / $pageSize);
$uploadLogs  = DbFunctions::getUploadLogsPage($pageSize, $offset, $isAdmin, $isMod);

// Smarty-Variablen zuweisen
$smarty->assign('upload_logs',  $uploadLogs);
$smarty->assign('isAdmin',      $isAdmin);
$smarty->assign('isMod',        $isMod);
$smarty->assign('username',     $_SESSION['username'] ?? '');
$smarty->assign('currentPage',  $currentPage);
$smarty->assign('totalPages',   $totalPages);

// Template anzeigen
$smarty->display('upload_logs.tpl');
