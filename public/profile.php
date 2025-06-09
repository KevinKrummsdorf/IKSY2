<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/db.inc.php';

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Profilinformationen laden oder bei Bedarf erstellen
$profile = DbFunctions::getOrCreateUserProfile($userId);

// ❗ 2FA-Logik nur hier gezielt einbinden
require_once __DIR__ . '/../includes/2fa.inc.php';

// Allgemeine Smarty-Daten
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile);

// Seite anzeigen
$smarty->display('profile.tpl');
