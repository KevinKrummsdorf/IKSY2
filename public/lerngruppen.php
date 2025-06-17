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

// Gruppenaktionen verarbeiten (Erstellen/Beitreten)
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
                    DbFunctions::setUserRoleInGroup($groupId, $userId, 'member');
                    $success = 'Du bist der Gruppe beigetreten.';
                } else {
                    $error = 'Fehler: dem Gruppenbeitritt ist fehlgeschlagen.';
                }
            }
        }
    }
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
$smarty->display('lerngruppen.tpl');

