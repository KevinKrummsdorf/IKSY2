<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriffsschutz: Nur für eingeloggte Nutzer
if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um Lerngruppen nutzen zu können.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId  = (int)$_SESSION['user_id'];
$error   = '';
$success = '';

// Gruppenaktionen verarbeiten (Erstellen/Beitreten/Verlassen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_group'])) {
        $groupName = trim($_POST['group_name'] ?? '');
        if ($groupName === '') {
            $error = 'Bitte gib einen Gruppennamen ein.';
        } elseif (DbFunctions::fetchGroupByName($groupName)) {
            $error = 'Dieser Gruppenname ist bereits vergeben.';
        } else {
            $newGroupId = DbFunctions::createGroup($groupName, $userId);
            if ($newGroupId) {
                $success = 'Die Gruppe wurde erfolgreich erstellt.';
            } else {
                $error = 'Fehler: Gruppe konnte nicht erstellt werden.';
            }
        }
    } elseif (isset($_POST['join_group'])) {
        $groupName = trim($_POST['group_name'] ?? '');
        if ($groupName === '') {
            $error = 'Bitte gib einen Gruppenname ein.';
        } else {
            $group = DbFunctions::fetchGroupByName($groupName);
            if (!$group) {
                $error = 'Keine Gruppe mit diesem Namen gefunden.';
            } else {
                $groupId = (int)$group['id'];
                if (DbFunctions::addUserToGroup($groupId, $userId)) {
                    $success = 'Du bist der Gruppe beigetreten.';
                } else {
                    $error = 'Fehler: dem Gruppenbeitritt ist fehlgeschlagen.';
                }
            }
        }
    } elseif (isset($_POST['leave_group'])) {
        $currentGroup = DbFunctions::fetchGroupByUser($userId);
        if ($currentGroup && DbFunctions::removeUserFromGroup((int)$currentGroup['id'], $userId)) {
            $success = 'Du hast die Gruppe verlassen.';
        } else {
            $error = 'Fehler: Konnte die Gruppe nicht verlassen.';
        }
    }
}

// Aktuelle Gruppeninformationen abrufen
$currentGroup = DbFunctions::fetchGroupByUser($userId);
$members      = [];
$groupFiles   = [];
if ($currentGroup) {
    $groupId    = (int)$currentGroup['id'];
    $members    = DbFunctions::getGroupMembers($groupId);
    $groupFiles = DbFunctions::getUploadsByGroup($groupId);
}

// Smarty-Variablen zuweisen
$smarty->assign([
    'group'         => $currentGroup,
    'members'       => $members,
    'group_uploads' => $groupFiles,
]);
if ($error !== '') {
    $smarty->assign('error', $error);
}
if ($success !== '') {
    $smarty->assign('success', $success);
}

// Seite anzeigen
$smarty->display('lerngruppen.tpl');

