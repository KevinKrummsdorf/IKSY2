<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriffsschutz: Nur für eingeloggte Nutzer
if (empty($_SESSION['user_id'])) {
    $reason = "Du musst eingeloggt sein, um Lerngruppen nutzen zu können.";
    handle_error(401, $reason, 'both');
}

$userId  = (int)$_SESSION['user_id'];
$error   = '';
$success = '';

// Gruppenaktionen verarbeiten (Erstellen/Beitreten)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_group'])) {
        $groupName = trim($_POST['group_name'] ?? '');
        $joinType  = $_POST['join_type'] ?? 'open';
        $allowed   = ['open','invite','code'];
        if ($groupName === '') {
            $error = 'Bitte gib einen Gruppennamen ein.';
        } elseif (DbFunctions::fetchGroupByName($groupName)) {
            $error = 'Dieser Gruppenname ist bereits vergeben.';
        } elseif (!in_array($joinType, $allowed, true)) {
            $error = 'Ungültige Beitrittsart.';
        } else {
            $inviteCode = null;
            if ($joinType === 'code') {
                $inviteCode = bin2hex(random_bytes(5));
            }
            $newGroupId = DbFunctions::createGroup($groupName, $userId, $joinType, $inviteCode);
            if ($newGroupId) {
                $success = 'Die Gruppe wurde erfolgreich erstellt.';
                if ($inviteCode) {
                    $success .= ' Einladungscode: ' . htmlspecialchars($inviteCode, ENT_QUOTES);
                }
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
                if ($group['join_type'] !== 'open') {
                    $error = 'Dieser Gruppe kann man nicht direkt beitreten.';
                } elseif (DbFunctions::addUserToGroup($groupId, $userId)) {
                    DbFunctions::setUserRoleInGroup($groupId, $userId, 'member');
                    $success = 'Du bist der Gruppe beigetreten.';
                } else {
                    $error = 'Fehler: dem Gruppenbeitritt ist fehlgeschlagen.';
                }
            }
        }
    }
}

// Erfolgreiches Löschen anzeigen
if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $success = 'Die Gruppe wurde erfolgreich gelöscht.';
}

// Alle Gruppen des Nutzers abrufen
$myGroups = DbFunctions::fetchGroupsByUser($userId);

// Smarty-Variablen zuweisen
$smarty->assign([
    'myGroups' => $myGroups,
]);
if ($error !== '') {
    $smarty->assign('error', $error);
}
if ($success !== '') {
    $smarty->assign('success', $success);
}

// Seite anzeigen
$smarty->display('my_groups.tpl');

