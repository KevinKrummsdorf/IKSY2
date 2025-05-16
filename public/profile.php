<?php
declare(strict_types=1);


// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';


// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Profil zu sehen.");
    header("Location: {$config['base_url']}/error/403?reason={$reason}&action=both");    exit;
}

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
