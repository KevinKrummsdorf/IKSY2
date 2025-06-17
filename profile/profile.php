<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

// Login-Schutz (für eigenes Profil weiterhin erforderlich)
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Profil zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

// Eingeloggter Benutzer (für layout.tpl)
$smarty->assign('username', $_SESSION['username']);
$smarty->assign('isLoggedIn', true);

// Standardmäßig eigenes Profil laden
$profileUserId = $_SESSION['user_id'];

// Prüfen, ob über URL jemand anderes angefordert wird (z.B. profile.php?id=5)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileUserId = (int) $_GET['id'];
}

// Profil abrufen (aus users + profile)
$profile = DbFunctions::getOrCreateUserProfile($profileUserId);

// Optional: Prüfen, ob es das eigene Profil ist
$isOwnProfile = ($profileUserId === (int)$_SESSION['user_id']);

$pwSuccess = null;
$pwMessage = null;

// Passwortänderung nur für eigenes Profil erlauben
if ($isOwnProfile && ($_POST['action'] ?? '') === 'change_password') {
    $old     = $_POST['old_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['new_password_confirm'] ?? '';
    $log = LoggerFactory::get('password_change');
    
    try {
        if ($old === '' || $new === '' || $confirm === '') {
            throw new RuntimeException('Fehlende Eingaben');
        }
        if ($new !== $confirm) {
            throw new RuntimeException('Passwörter stimmen nicht überein');
        }
        PasswordController::changePassword((int)$_SESSION['user_id'], $old, $new);
        $pwSuccess = 'Passwort wurde aktualisiert.';
    } catch (Throwable $e) {
        $log->error('Passwort ändern fehlgeschlagen', ['error' => $e->getMessage()]);
        $pwMessage = defined('DEBUG') ? $e->getMessage() : 'Fehler beim Ändern des Passworts.';
    }
}

// 2FA-Daten nur für eigenes Profil zuweisen
if ($isOwnProfile) {
    require_once __DIR__ . '/../includes/2fa.inc.php';
    $smarty->assign('twofa_enabled', $twofa_enabled ?? false);
    $smarty->assign('show_2fa_form', $show_2fa_form ?? false);
    $smarty->assign('qrCodeUrl', $qrCodeUrl ?? '');
} else {
    // Für fremde Profile keine 2FA-Daten anzeigen
    $smarty->assign('twofa_enabled', false);
    $smarty->assign('show_2fa_form', false);
    $smarty->assign('qrCodeUrl', '');
}

// Allgemeine Smarty-Zuweisungen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('profile', $profile); // für {$profile.username}, {$profile.first_name} usw.
$smarty->assign('profile_username', $profile['username'] ?? ''); // explizit für Profilinhaber
$smarty->assign('isOwnProfile', $isOwnProfile);
$smarty->assign('pw_success', $pwSuccess);
$smarty->assign('pw_message', $pwMessage);

// Seite anzeigen
$smarty->display('profile.tpl');
