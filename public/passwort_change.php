<?php
declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Du musst eingeloggt sein, um dein Passwort zu ändern.';
    handle_error(403, $reason, 'both');
}

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old      = $_POST['old_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['new_password_confirm'] ?? '';
    try {
        if ($old === '' || $new === '' || $confirm === '') {
            throw new RuntimeException('Fehlende Eingaben');
        }
        if ($new !== $confirm) {
            throw new RuntimeException('Passwörter stimmen nicht überein');
        }
        PasswordController::changePassword((int)$_SESSION['user_id'], $old, $new);
        $success = true;
    } catch (Throwable $e) {
        $message = defined('DEBUG') ? $e->getMessage() : 'Fehler beim Ändern des Passworts.';
    }
}

$smarty->assign('success', $success);
$smarty->assign('message', $message);
$smarty->display('passwort_change.tpl');
