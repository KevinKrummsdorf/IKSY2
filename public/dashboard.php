<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/calendar.inc.php';

$pdo = DbFunctions::db_connect();

// Zugriffsschutz: Login + 2FA erforderlich
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['2fa_passed']) ||
    $_SESSION['2fa_passed'] !== true
) {
    http_response_code(403);
    exit('Zugriff verweigert');
}

// Rollen prüfen
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

// Logs laden über DbFunctions – bei jedem Aufruf!
$contactRequests = $isAdmin ? DbFunctions::getRecentContactRequests(10) : [];
$lockedUsers     = $isAdmin ? DbFunctions::getAllLockedUsers() : [];
$pendingUploads  = DbFunctions::getPendingUploads();
$pendingCourses  = DbFunctions::getPendingCourseSuggestions();
$userUploads     = DbFunctions::getApprovedUploadsByUser((int)$_SESSION['user_id']);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Dateityp fuer Vorschau bestimmen
foreach ($userUploads as &$upload) {
    $ext = strtolower(pathinfo($upload['stored_name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $upload['type'] = 'image';
    } elseif ($ext === 'pdf') {
        $upload['type'] = 'pdf';
    } else {
        $upload['type'] = 'other';
    }
}
unset($upload);

// Kalender- und Tagesansicht laden
assignUserCalendarToSmarty($pdo, $smarty);
assignTodayTodosToSmarty($pdo, $smarty);

// Flash anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Smarty-Variablen zuweisen
$smarty->assign('contact_requests', $contactRequests);
$smarty->assign('locked_users',     $lockedUsers);
$smarty->assign('pending_uploads',  $pendingUploads);
$smarty->assign('pending_courses',  $pendingCourses);
$smarty->assign('user_uploads',     $userUploads);
$smarty->assign('isAdmin',          $isAdmin);
$smarty->assign('isMod',            $isMod);
$smarty->assign('csrf_token',       $_SESSION['csrf_token']);

// Seite anzeigen
$smarty->display('dashboard.tpl');
