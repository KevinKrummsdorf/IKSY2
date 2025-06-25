<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';


if (empty($_SESSION['user_id'])) {
    $reason = 'Du musst eingeloggt sein, um einer Gruppe beizutreten.';
    handle_error(401, $reason, 'both');
}

$token = trim((string)($_GET['token'] ?? ''));

$data = [
    'alertType'  => 'danger',
    'message'    => 'Ungültiger Einladungslink.',
    'showButton' => true,
    'buttonText' => 'Zur Startseite',
    'buttonLink' => 'index.php',
];

try {
    if ($token === '') {
        throw new RuntimeException('Token fehlt');
    }

    $invite = DbFunctions::fetchGroupInviteByToken($token);
    if (!$invite) {
        throw new RuntimeException('Einladung ungültig oder abgelaufen');
    }

    if ((int)$invite['invited_user_id'] !== (int)$_SESSION['user_id']) {
        throw new RuntimeException('Diese Einladung ist nicht für deinen Account.');
    }

    $group = DbFunctions::fetchGroupById((int)$invite['group_id']);
    if (!$group || $group['join_type'] !== 'invite') {
        throw new RuntimeException('Diese Gruppe erlaubt keinen Beitritt per Einladung.');
    }

    DbFunctions::addUserToGroup((int)$invite['group_id'], (int)$invite['invited_user_id']);
    DbFunctions::setUserRoleInGroup((int)$invite['group_id'], (int)$invite['invited_user_id'], 'member');
    DbFunctions::markGroupInviteUsed((int)$invite['id']);

    $data['alertType']  = 'success';
    $data['message']    = 'Du bist der Gruppe ' . htmlspecialchars($group['name'], ENT_QUOTES) . ' beigetreten.';
    $data['buttonText'] = 'Zur Gruppe';
    $data['buttonLink'] = 'groups/' . rawurlencode($group['name']);
} catch (Throwable $e) {
    error_log('join_group failed: ' . $e->getMessage());
}

$smarty->assign($data);
$smarty->display('join_group.tpl');
