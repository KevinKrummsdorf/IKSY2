<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/csrf.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

$token = trim($_GET['token'] ?? '');
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    try {
        if ($token === '' || $password === '' || $confirm === '') {
            throw new RuntimeException('Fehlende Eingaben');
        }
        if ($password !== $confirm) {
            throw new RuntimeException('Passwörter stimmen nicht überein');
        }
        PasswordController::resetPassword($token, $password);
        $success = true;
    } catch (Throwable $e) {
        $message = trim($e->getMessage());
    }
}

$smarty->assign('token', $token);
$smarty->assign('success', $success);
$smarty->assign('message', $message);
$smarty->display('reset_password.tpl');
