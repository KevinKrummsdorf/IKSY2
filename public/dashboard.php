<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

session_start();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['2fa_passed']) ||
    $_SESSION['2fa_passed'] !== true
) {
    header('Location: index.php');
    exit;
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// DB-Verbindung
$pdo = DbFunctions::db_connect();

// Logs laden
$loginLogs       = fetchLoginLogs($pdo, $isAdmin, 10);
$captchaLogs     = fetchCaptchaLogs($pdo, $isAdmin, 10);
$contactRequests = $isAdmin ? getRecentContactRequests($pdo, 10) : [];
$uploadLogs      = fetchUploadLogs($pdo, $isAdmin, $isMod, 10);

// Flash anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Smarty-Variablen
$smarty->assign('login_logs',       $loginLogs);
$smarty->assign('captcha_logs',     $captchaLogs);
$smarty->assign('contact_requests', $contactRequests);
$smarty->assign('upload_logs',      $uploadLogs);
$smarty->assign('isAdmin',          $isAdmin);
$smarty->assign('isMod',            $isMod);

// Seite anzeigen
$smarty->display('dashboard.tpl');
