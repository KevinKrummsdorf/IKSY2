<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode('Du musst eingeloggt sein, um dein Profil zu bearbeiten.');
    header("Location: {$config['base_url']}/error.php?code=403&reason={$reason}&action=both");
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile = DbFunctions::getOrCreateUserProfile($userId);
$userData = DbFunctions::fetchUserById($userId);
$email    = $userData['email'] ?? '';
$socialEntries = DbFunctions::getUserSocialMedia($userId);
$socials = [];
foreach ($socialEntries as $entry) {
    $socials[$entry['platform']] = $entry['username'];
}

// CSRF-Token erzeugen
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$smarty->assign('csrf_token', $_SESSION['csrf_token']);

// Smarty-Zuweisungen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile);
$smarty->assign('email', $email);
$smarty->assign('socials', $socials);
$smarty->assign('max_birthdate', (new DateTime('-16 years'))->format('Y-m-d'));

// Flash Message anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Template anzeigen
$smarty->display('edit_profile.tpl');


