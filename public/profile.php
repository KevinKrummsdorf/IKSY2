<?php
declare(strict_types=1);

// Zentrale Initialisierung
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/PasswordController.php';

// Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Profil zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

$profile = DbFunctions::getOrCreateUserProfile($userId);

$pwSuccess = null;
$pwMessage = null;

if (($_POST['action'] ?? '') === 'change_password') {
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

// 2FA-Logik nur hier gezielt einbinden
require_once __DIR__ . '/../includes/2fa.inc.php';

// Allgemeine Smarty-Daten
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username', $username);
$smarty->assign('profile', $profile);
$smarty->assign('pw_success', $pwSuccess);
$smarty->assign('pw_message', $pwMessage);

// Seite anzeigen
$smarty->display('profile.tpl');
