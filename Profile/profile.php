<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/db.inc.php'; // 🔧 Wichtig: DB-Funktionen laden

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Profil zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 🔧 Aktuelles Profil aus der DB laden
$profile = DbFunctions::fetchUserProfile($userId);

// 🔐 2FA optional einbinden
require_once __DIR__ . '/../includes/2fa.inc.php';

// Smarty-Daten setzen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile); // ✅ Jetzt korrekt eingebunden

// Template anzeigen
$smarty->display('profile.tpl');
