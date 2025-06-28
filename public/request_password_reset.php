<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    try {
        if ($identifier === '') {
            throw new RuntimeException('Feld leer');
        }
        PasswordController::requestReset($identifier);
        $success = true;
    } catch (Throwable $e) {
        $message = defined('DEBUG') ? $e->getMessage() : 'Fehler beim Versenden der E-Mail.';
    }
}

$smarty->assign('success', $success);
$smarty->assign('message', $message);
$smarty->display('request_password_reset.tpl');
