<?php
declare(strict_types=1);
<<<<<<< HEAD

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

session_start();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['2fa_passed']) ||
    $_SESSION['2fa_passed'] !== true
) {
=======

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id'])) {
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
    header('Location: index.php');
    exit;
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// DB-Verbindung
$pdo = DbFunctions::db_connect();

<<<<<<< HEAD
// Logs laden
=======
// Logs laden (werden intern auf Admin-/Modrechte geprüft)
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
$loginLogs       = fetchLoginLogs($pdo, $isAdmin, 10);
$captchaLogs     = fetchCaptchaLogs($pdo, $isAdmin, 10);
$contactRequests = $isAdmin ? getRecentContactRequests($pdo, 10) : [];
$uploadLogs      = fetchUploadLogs($pdo, $isAdmin, $isMod, 10);

<<<<<<< HEAD
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

=======
// Smarty-Variablen zuweisen
$smarty->assign('login_logs',        $loginLogs);
$smarty->assign('captcha_logs',      $captchaLogs);
$smarty->assign('contact_requests',  $contactRequests);
$smarty->assign('upload_logs',       $uploadLogs);
$smarty->assign('isAdmin',           $isAdmin);
$smarty->assign('isMod',             $isMod);

>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
// Seite anzeigen
$smarty->display('dashboard.tpl');
