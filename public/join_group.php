<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';

$log = LoggerFactory::get('join_group');

if (empty($_SESSION['user_id'])) {
    $reason = urlencode('Du musst eingeloggt sein, um einer Gruppe beizutreten.');
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
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
        throw new RuntimeException('Diese Einladung ist nicht f\xC3\xBCr deinen Account.');
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
    $data['buttonLink'] = 'gruppe.php?id=' . (int)$invite['group_id'];
} catch (Throwable $e) {
    $log->error('join_group failed', ['error' => $e->getMessage()]);
}

$smarty->assign($data);
$smarty->display('join_group.tpl');
