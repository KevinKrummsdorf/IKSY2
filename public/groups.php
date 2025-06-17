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

// POST: Gruppe erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['group_name'] ?? '');
    if ($name === '') {
        $error = 'Bitte gib einen Gruppennamen ein.';
    } elseif (DbFunctions::fetchGroupByName($name)) {
        $error = 'Dieser Gruppenname ist bereits vergeben.';
    } else {
        $newId = DbFunctions::createGroup($name, $userId);
        if ($newId) {
            header("Location: /studyhub/gruppe.php?id={$newId}");
            exit;
        } else {
            $error = 'Fehler: Gruppe konnte nicht erstellt werden.';
        }
    }
}

// POST: Gruppe beitreten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_group'])) {
    $name = trim($_POST['group_name'] ?? '');
    if ($name === '') {
        $error = 'Bitte gib einen Gruppennamen ein.';
    } else {
        $group = DbFunctions::fetchGroupByName($name);
        if (!$group) {
            $error = 'Keine Gruppe mit diesem Namen gefunden.';
        } else {
            $id = (int)$group['id'];
            if (DbFunctions::addUserToGroup($id, $userId)) {
                DbFunctions::setUserRoleInGroup($id, $userId, 'member');
                header("Location: /studyhub/gruppe.php?id={$id}");
                exit;
            } else {
                $error = 'Fehler beim Beitreten.';
            }
        }
    }
}

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
