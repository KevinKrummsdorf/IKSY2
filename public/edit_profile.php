<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode('Du musst eingeloggt sein, um dein Profil zu bearbeiten.');
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Profildaten laden
$profile = DbFunctions::getOrCreateUserProfile($userId);
$userData = DbFunctions::fetchUserById($userId);
$email    = $userData['email'] ?? '';



// Smarty-Zuweisungen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile);
$smarty->assign('email', $email);

// Template anzeigen
$smarty->display('edit_profile.tpl');


