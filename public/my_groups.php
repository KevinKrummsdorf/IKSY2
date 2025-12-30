<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/GroupRepository.php';

// Zugriffsschutz: Nur für eingeloggte Nutzer
if (empty($_SESSION['user_id'])) {
    $reason = "Du musst eingeloggt sein, um Lerngruppen nutzen zu können.";
    handle_error(401, $reason, 'both');
}

$userId  = (int)$_SESSION['user_id'];
$error   = '';
$success = '';
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$db = new Database();
$groupRepository = new GroupRepository($db);

// Gruppenaktionen verarbeiten (Erstellen/Beitreten)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Ungültiger CSRF-Token.';
    } elseif (isset($_POST['create_group'])) {
        $groupName = trim($_POST['group_name'] ?? '');
        $joinType  = $_POST['join_type'] ?? 'open';
        $allowed   = ['open','invite','code'];
        if ($groupName === '') {
            $error = 'Bitte gib einen Gruppennamen ein.';
        } elseif ($groupRepository->fetchGroupByName($groupName)) {
            $error = 'Dieser Gruppenname ist bereits vergeben.';
        } elseif (!in_array($joinType, $allowed, true)) {
            $error = 'Ungültige Beitrittsart.';
        } else {
            $inviteCode = null;
            if ($joinType === 'code') {
                $inviteCode = bin2hex(random_bytes(5));
            }
            $newGroupId = $groupRepository->createGroup($groupName, $userId, $joinType, $inviteCode);
            if ($newGroupId) {
                $success = 'Die Gruppe wurde erfolgreich erstellt.';
                if ($inviteCode) {
                    $success .= ' Einladungscode: ' . htmlspecialchars($inviteCode, ENT_QUOTES);
                }

                if (!empty($_FILES['group_picture']) && $_FILES['group_picture']['error'] === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['group_picture']['tmp_name'];
                    $ext = strtolower(pathinfo($_FILES['group_picture']['name'], PATHINFO_EXTENSION));

                    $mime = '';
                    if (function_exists('finfo_open')) {
                        $f = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($f, $tmp) ?: '';
                        finfo_close($f);
                    } elseif (function_exists('mime_content_type')) {
                        $mime = mime_content_type($tmp);
                    }
                    $allowed = ['image/jpeg','image/png','image/gif','image/x-png'];
                    if (!$mime || in_array($mime, $allowed, true)) {
                        $dir = __DIR__ . '/../uploads/group_pictures/';
                        if (!is_dir($dir)) {
                            mkdir($dir, 0775, true);
                        }
                        $fileName = uniqid('group_', true) . '.' . $ext;
                        $target   = $dir . $fileName;
                        if (move_uploaded_file($tmp, $target) || rename($tmp, $target)) {
                            $groupRepository->updateGroup((int)$newGroupId, ['group_picture' => $fileName]);
                        }
                    }
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
            $group = $groupRepository->fetchGroupByName($groupName);
            if (!$group) {
                $error = 'Keine Gruppe mit diesem Namen gefunden.';
            } else {
                $groupId = (int)$group['id'];
                if ($group['join_type'] !== 'open') {
                    $error = 'Dieser Gruppe kann man nicht direkt beitreten.';
                } elseif ($groupRepository->addUserToGroup($groupId, $userId)) {
                    $groupRepository->setUserRoleInGroup($groupId, $userId, 'member');
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
$myGroups = $groupRepository->fetchGroupsByUser($userId);

// Smarty-Variablen zuweisen
$smarty->assign([
    'myGroups'   => $myGroups,
    'csrf_token' => $csrf,
]);
if ($error !== '') {
    $smarty->assign('error', $error);
}
if ($success !== '') {
    $smarty->assign('success', $success);
}

// Seite anzeigen
$smarty->display('my_groups.tpl');
