<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Config-Werte an Smarty übergeben
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);

// Session-Status an Smarty übergeben
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username', $_SESSION['username'] ?? '');
$smarty->display('profile.tpl');
?>