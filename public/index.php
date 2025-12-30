<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.inc.php';

// ✅ Weiterleitung bei erfolgreichem Login + 2FA
if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['2fa_passed'] ?? false) === true
) {
    header('Location: ' . build_url('dashboard'));
    exit;
}

// Flash anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Optionales Modal anzeigen (z. B. nach Fehlerseite)
$show = $_GET['show'] ?? null;
if (in_array($show, ['login', 'register'], true)) {
    $smarty->assign('show_modal', $show);
}

$smarty->display('index.tpl');
