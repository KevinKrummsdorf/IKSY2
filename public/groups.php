<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um deine Gruppen zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$userId = $_SESSION['user_id'];
$error   = '';
$success = '';


// Alle Gruppen für Liste
$allGroups = DbFunctions::fetchAllGroups();

$smarty->assign([
    'groups'  => $allGroups,
    'error'   => $error,
    'success' => $success,
]);

if ($error) {
    $smarty->assign('error', $error);
}
if ($success) {
    $smarty->assign('success', $success);
}

$smarty->display('groups.tpl');
