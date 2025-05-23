<?php
declare(strict_types=1);

// Zentrale Initialisierung & Session
require_once __DIR__ . '/../includes/config.inc.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Zugriffsprüfung: nur Admins und Mods
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'mod'], true)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Zugriff verweigert.');
}

// Pagination-Parameter
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage     = 25;
$offset      = ($currentPage - 1) * $perPage;

// Gesamtanzahl & Kontaktanfragen über DbFunctions
$totalCount     = DbFunctions::countContactRequests();
$totalPages     = (int)ceil($totalCount / $perPage);
$pageRequests   = DbFunctions::getContactRequestsPage($perPage, $offset);

// Lösch-Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    DbFunctions::deleteContactRequest((string)$_POST['delete_id']);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $currentPage);
    exit;
}

// Template-Daten zuweisen
$smarty->assign('contact_requests', $pageRequests);
$smarty->assign('currentPage',      $currentPage);
$smarty->assign('totalPages',       $totalPages);
$smarty->assign('isAdmin',          $role === 'admin');
$smarty->assign('isMod',            $role === 'mod');
$smarty->assign('username',         $_SESSION['username'] ?? '');

// Template anzeigen
$smarty->display('contact_requests.tpl');
