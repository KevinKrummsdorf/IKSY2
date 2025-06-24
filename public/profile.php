<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Profil zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

// Standardmäßig eigenes Profil laden
$profileUserId = $userId;

// Fremdprofil über GET laden
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileUserId = (int) $_GET['id'];
} elseif (isset($_GET['user'])) {
    $other = DbFunctions::fetchUserByIdentifier($_GET['user']);
    if ($other) {
        $profileUserId = (int)$other['id'];
    }
}

// Profil abrufen
$profile = DbFunctions::getOrCreateUserProfile($profileUserId);

// Alter berechnen, falls Geburtsdatum vorhanden ist
if (!empty($profile['birthdate'])) {
    try {
        $birth = new DateTime($profile['birthdate']);
        $profile['age'] = $birth->diff(new DateTime('today'))->y;
    } catch (Throwable $e) {
        $profile['age'] = null;
    }
}

// Username des Profilbesitzers abrufen
$profileOwner = DbFunctions::fetchUserById($profileUserId);
if ($profileOwner) {
    if (isset($profileOwner['username'])) {
        $profile['username'] = $profileOwner['username'];
    }
    if (isset($profileOwner['email'])) {
        $profile['email'] = $profileOwner['email'];
    }
}

// Prüfen, ob es das eigene Profil ist
$isOwnProfile = ($profileUserId === $userId);
$entries = DbFunctions::getUserSocialMedia($profileUserId);
$socialEntries = [];
foreach ($entries as $s) {
    $socialEntries[$s['platform']] = $s['username'];
}

$pwSuccess = null;
$pwMessage = null;

// Passwortänderung nur für eigenes Profil erlauben
if ($isOwnProfile && ($_POST['action'] ?? '') === 'change_password') {
    $old     = $_POST['old_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['new_password_confirm'] ?? '';
    try {
        if ($old === '' || $new === '' || $confirm === '') {
            throw new RuntimeException('Fehlende Eingaben');
        }
        if ($new !== $confirm) {
            throw new RuntimeException('Passwörter stimmen nicht überein');
        }
        PasswordController::changePassword($userId, $old, $new);
        $pwSuccess = 'Passwort wurde aktualisiert.';
    } catch (Throwable $e) {
        $pwMessage = defined('DEBUG') ? $e->getMessage() : 'Fehler beim Ändern des Passworts.';
    }
}

// 2FA nur für eigenes Profil laden
if ($isOwnProfile) {
    require_once __DIR__ . '/../includes/2fa.inc.php';
    $smarty->assign('twofa_enabled', $twofa_enabled ?? false);
    $smarty->assign('show_2fa_form', $show_2fa_form ?? false);
    $smarty->assign('qrCodeUrl', $qrCodeUrl ?? '');
} else {
    $smarty->assign('twofa_enabled', false);
    $smarty->assign('show_2fa_form', false);
    $smarty->assign('qrCodeUrl', '');
}

// Allgemeine Smarty-Daten
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile);
$smarty->assign('socials', $socialEntries);
$smarty->assign('isOwnProfile', $isOwnProfile);
$smarty->assign('max_birthdate', (new DateTime('-16 years'))->format('Y-m-d'));
$smarty->assign('isAdmin', $isAdmin);
$smarty->assign('pw_success', $pwSuccess);
$smarty->assign('pw_message', $pwMessage);

// Flash Message anzeigen
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Seite anzeigen
$smarty->display('profile.tpl');
