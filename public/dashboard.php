<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Zugriffsschutz: Login + 2FA erforderlich
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['2fa_passed']) ||
    $_SESSION['2fa_passed'] !== true
) {
    $reason = urlencode("Du musst vollständig eingeloggt sein, um das Dashboard zu nutzen.");
    header("Location: /error/403?reason={$reason}&action=both");
    exit;
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// Logs laden über DbFunctions
$loginLogs       = DbFunctions::fetchLoginLogs($isAdmin, 10);
$captchaLogs     = DbFunctions::fetchCaptchaLogs($isAdmin, 10);
$contactRequests = $isAdmin ? DbFunctions::getRecentContactRequests(10) : [];
$uploadLogs      = DbFunctions::getUploadLogsPage(10, 0, $isAdmin, $isMod);
$lockedUsers     = $isAdmin ? DbFunctions::getAllLockedUsers() : [];

// Flash anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Smarty-Variablen zuweisen
$smarty->assign('login_logs',       $loginLogs);
$smarty->assign('captcha_logs',     $captchaLogs);
$smarty->assign('contact_requests', $contactRequests);
$smarty->assign('upload_logs',      $uploadLogs);
$smarty->assign('locked_users',     $lockedUsers);
$smarty->assign('isAdmin',          $isAdmin);
$smarty->assign('isMod',            $isMod);

// Seite anzeigen
$smarty->display('dashboard.tpl');
