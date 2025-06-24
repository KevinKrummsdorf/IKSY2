<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// ✅ Weiterleitung bei erfolgreichem Login + 2FA
if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['2fa_passed'] ?? false) === true
) {
    header('Location: ' . url_for('dashboard'));
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
