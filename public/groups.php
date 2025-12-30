<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/GroupRepository.php';

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = "Du musst eingeloggt sein, um deine Gruppen zu sehen.";
    handle_error(401, $reason, 'both');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$userId = $_SESSION['user_id'];
$error   = '';
$success = '';

$db = new Database();
$groupRepository = new GroupRepository($db);

// Alle Gruppen für Liste
$allGroups = $groupRepository->fetchAllGroups();

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
