<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um Dateien hochladen zu können.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId  = (int)$_SESSION['user_id'];
$groupId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error   = '';
$success = '';

// Gruppe laden
$group = DbFunctions::fetchGroupById($groupId);
if (!$group) {
    http_response_code(404);
    echo 'Gruppe nicht gefunden';
    exit;
}

// Rolle des Users (admin/member/none)
$myRole = DbFunctions::fetchUserRoleInGroup($groupId, $userId) ?? 'none';

// POST-Aktionen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Beitreten
    if (isset($_POST['join_group']) && $myRole === 'none') {
        if ($group['join_type'] === 'open' || ($group['join_type'] === 'code' && ($_POST['invite_code'] ?? '') === $group['invite_code'])) {
            if (DbFunctions::addUserToGroup($groupId, $userId)) {
                DbFunctions::setUserRoleInGroup($groupId, $userId, 'member');
                $success = 'Beigetreten.';
                $myRole = 'member';
            } else {
                $error = 'Fehler beim Beitreten.';
            }
        } elseif ($group['join_type'] === 'code') {
            $error = 'Ungültiger Einladungscode.';
        } else {
            $error = 'Dieser Gruppe kann nur per Einladung beigetreten werden.';
        }
    }
    // Verlassen
    elseif (isset($_POST['leave_group']) && $myRole !== 'none') {
        DbFunctions::removeUserFromGroup($groupId, $userId);
        $success = 'Gruppe verlassen.';
        $myRole = 'none';
    }
    // Löschen (nur Admin)
    elseif (isset($_POST['delete_group']) && $myRole === 'admin') {
        if (DbFunctions::deleteGroup($groupId)) {
            header('Location: lerngruppen?deleted=1');
            exit;
        }
        $error = 'Konnte Gruppe nicht löschen.';
    }
    // Mitglied entfernen (nur Admin)
    elseif (isset($_POST['remove_member']) && $myRole === 'admin') {
        $uid = (int)$_POST['user_id'];
        DbFunctions::removeUserFromGroup($groupId, $uid);
        $success = 'Mitglied entfernt.';
    }
    // Benutzer einladen (nur Admin)
    elseif (isset($_POST['invite_user']) && $myRole === 'admin') {
        $username = trim($_POST['invite_username'] ?? '');
        if ($username === '') {
            $error = 'Bitte gib einen Benutzernamen ein.';
        } else {
            try {
                $invUser = DbFunctions::fetchUserByIdentifier($username);
                if (!$invUser) {
                    $error = 'Benutzer nicht gefunden.';
                } elseif (DbFunctions::fetchUserRoleInGroup($groupId, (int)$invUser['id'])) {
                    $error = 'Benutzer ist bereits Mitglied.';
                } elseif (DbFunctions::fetchActiveGroupInvite($groupId, (int)$invUser['id'])) {
                    $error = 'Es besteht bereits eine aktive Einladung.';
                } else {
                    $token = bin2hex(random_bytes(32));
                    if (DbFunctions::createGroupInvite($groupId, (int)$invUser['id'], $token)) {
                        sendGroupInviteEmail(
                            $invUser['email'],
                            $invUser['username'],
                            $group['name'],
                            $_SESSION['username'],
                            $token
                        );
                        $success = 'Einladung versendet.';
                    } else {
                        $error = 'Einladung konnte nicht erstellt werden.';
                    }
                }
            } catch (Throwable $e) {
                $log = LoggerFactory::get('gruppe');
                $log->error('Invite user failed', ['error' => $e->getMessage()]);
                $error = 'Fehler beim Versenden der Einladung.';
            }
        }
    }
    // Upload-Link
    elseif (isset($_POST['upload_group']) && $myRole !== 'none') {
        header("Location: upload?group_id={$groupId}");
        exit;
    }
}

// Mitglieder + Uploads holen
$members = DbFunctions::getGroupMembers($groupId);
$uploads = DbFunctions::getUploadsByGroup($groupId);

if ($error) {
    $smarty->assign('error', $error);
}
if ($success) {
    $smarty->assign('success', $success);
}

$smarty->assign(compact('group','members','uploads','myRole','error','success'));
$smarty->display('gruppe.tpl');
