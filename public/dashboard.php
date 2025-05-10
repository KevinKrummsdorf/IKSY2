<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// DB-Verbindung
$pdo = DbFunctions::db_connect();

// Logs laden (werden intern auf Admin-/Modrechte geprüft)
$loginLogs       = fetchLoginLogs($pdo, $isAdmin, 10);
$captchaLogs     = fetchCaptchaLogs($pdo, $isAdmin, 10);
$contactRequests = $isAdmin ? getRecentContactRequests($pdo, 10) : [];
$uploadLogs      = fetchUploadLogs($pdo, $isAdmin, $isMod, 10);

// Smarty-Variablen zuweisen
$smarty->assign('login_logs',        $loginLogs);
$smarty->assign('captcha_logs',      $captchaLogs);
$smarty->assign('contact_requests',  $contactRequests);
$smarty->assign('upload_logs',       $uploadLogs);
$smarty->assign('isAdmin',           $isAdmin);
$smarty->assign('isMod',             $isMod);

// Seite anzeigen
$smarty->display('dashboard.tpl');
