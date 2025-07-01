<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = "Du musst eingeloggt sein, um Dateien hochladen zu können.";
    handle_error(401, $reason, 'both');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId  = (int)$_SESSION['user_id'];
$groupId = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $groupId = (int)$_GET['id'];
} elseif (isset($_GET['name'])) {
    $grp = DbFunctions::fetchGroupByName($_GET['name']);
    if ($grp) {
        $groupId = (int)$grp['id'];
    }
}
$error   = '';
$success = '';

// Gruppe laden
$group = DbFunctions::fetchGroupById($groupId);
if (!$group) {
    $reason = 'Gruppe nicht gefunden';
    handle_error(404, $reason);
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
                error_log('Invite user failed: ' . $e->getMessage());
                $error = 'Fehler beim Versenden der Einladung.';
            }
        }
    }
    // Upload löschen (nur Admin)
    elseif (isset($_POST['delete_upload']) && $myRole === 'admin') {
        $uId = (int)($_POST['upload_id'] ?? 0);
        if ($uId > 0) {
            try {
                $stored = DbFunctions::deleteGroupUpload($uId, $groupId, $userId);
                if ($stored !== null) {
                    $file = __DIR__ . '/../uploads/' . $stored;
                    if (is_file($file)) {
                        unlink($file);
                    }
                    $success = 'Upload gelöscht.';
                } else {
                    $error = 'Upload nicht gefunden.';
                }
            } catch (Exception $e) {
                $error = 'Fehler beim Löschen des Uploads.';
            }
        }
    }
    // Upload-Link
    elseif (isset($_POST['upload_group']) && $myRole !== 'none') {
        header("Location: upload.php?group_id={$groupId}");
        exit;
    }
    // neuen Gruppentermin erstellen
    elseif (isset($_POST['create_event']) && $myRole === 'admin') {
        $title  = trim($_POST['event_title'] ?? '');
        $date   = $_POST['event_date'] ?? '';
        $time   = $_POST['event_time'] ?? null;
        $time   = $time !== '' ? $time : null;
        $repeat = $_POST['event_repeat'] ?? 'none';
        if ($title === '' || $date === '') {
            $error = 'Titel und Datum erforderlich.';
        } elseif (DbFunctions::createGroupEvent($groupId, $title, $date, $time, $repeat)) {
            $success = 'Termin erstellt.';
        } else {
            $error = 'Termin konnte nicht erstellt werden.';
        }
    }
    // Gruppentermin löschen
    elseif (isset($_POST['delete_event']) && $myRole === 'admin') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        if ($eventId > 0 && DbFunctions::deleteGroupEvent($eventId, $groupId)) {
            $success = 'Termin gelöscht.';
        } else {
            $error = 'Termin konnte nicht gelöscht werden.';
        }
    }
    // Gruppenbild aktualisieren
    elseif (isset($_POST['update_picture'])) {
        if ($myRole !== 'admin') {
            $error = 'Nur Gruppen-Administratoren dürfen das Bild ändern.';
        } else {
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $error = 'Ungültiger CSRF-Token.';
            } elseif (empty($_FILES['group_picture']) || $_FILES['group_picture']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Kein Bild hochgeladen.';
            } else {
            $tmp  = $_FILES['group_picture']['tmp_name'];
            $ext  = strtolower(pathinfo($_FILES['group_picture']['name'], PATHINFO_EXTENSION));

            $mime = '';
            if (function_exists('finfo_open')) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($f, $tmp) ?: '';
                finfo_close($f);
            } elseif (function_exists('mime_content_type')) {
                $mime = mime_content_type($tmp);
            }
            $allowed = ['image/jpeg','image/png','image/gif','image/x-png'];
            if ($mime && !in_array($mime, $allowed, true)) {
                $error = 'Ungültiger Bildtyp.';
            } else {
                $dir = __DIR__ . '/../uploads/group_pictures/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }

                $fileName   = uniqid('group_', true) . '.' . $ext;
                $targetPath = $dir . $fileName;
                if (!move_uploaded_file($tmp, $targetPath)) {
                    if (!rename($tmp, $targetPath)) {
                        $error = 'Fehler beim Hochladen des Bildes.';
                    }
                }

                if ($error === '') {
                    if (!empty($group['group_picture'])) {
                        $old = __DIR__ . '/../uploads/group_pictures/' . $group['group_picture'];
                        if (is_file($old)) {
                            unlink($old);
                        }
                    }
                    DbFunctions::updateGroup($groupId, ['group_picture' => $fileName]);
                    $group['group_picture'] = $fileName;
                    $success = 'Gruppenbild aktualisiert.';
                }
            }
        }
    }
}

// Mitglieder + Uploads holen
$members = DbFunctions::getGroupMembers($groupId);
$uploads = DbFunctions::getUploadsByGroup($groupId);
$events  = DbFunctions::getGroupEventsByGroup($groupId);
foreach ($events as &$ev) {
    $ev['repeat_label'] = match ($ev['repeat_interval']) {
        'weekly'   => 'Wöchentlich',
        'biweekly' => 'Alle 2 Wochen',
        'monthly'  => 'Monatlich',
        default    => ''
    };
}
unset($ev);

if ($error) {
    $smarty->assign('error', $error);
}
if ($success) {
    $smarty->assign('success', $success);
}

$smarty->assign(compact('group','members','uploads','events','myRole','error','success'));
$smarty->assign('csrf_token', $_SESSION['csrf_token']);
$smarty->display('gruppe.tpl');
