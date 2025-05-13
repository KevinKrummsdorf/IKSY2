<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';

// Login-Schutz
<<<<<<< HEAD
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
=======
if (empty($_SESSION['user_id'])) {
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
    header('Location: index.php');
    exit;
}

<<<<<<< HEAD
$username = $_SESSION['username'];

// ❗ 2FA-Logik nur hier gezielt einbinden
require_once __DIR__ . '/../includes/2fa.inc.php';

// Allgemeine Smarty-Daten
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);

// Seite anzeigen
$smarty->display('profile.tpl');
=======
// Config-Werte an Smarty übergeben
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);

// Session-Status an Smarty übergeben
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username', $_SESSION['username'] ?? '');
$smarty->display('profile.tpl');
?>
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
