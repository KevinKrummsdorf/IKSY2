<?php

declare(strict_types=1);

use RobThree\Auth\TwoFactorAuth;

/**
 * Verarbeitet 2FA-bezogene Aktionen in der Profilseite oder beim Login:
 * - Setup starten (QR generieren)
 * - Code bestätigen
 * - 2FA deaktivieren
 *
 * Benötigt:
 * - $_SESSION['username'] oder $_SESSION['2fa_user']
 * - $_POST['action'] (optional)
 */

$username = $_SESSION['username'] ?? $_SESSION['2fa_user'] ?? null;

if (!$username) {
    return; // kein Benutzerkontext vorhanden
}

// Ergebnis-Variablen für Smarty vorbereiten
$twofa_enabled = DbFunctions::isTwoFAEnabled($username);
$show_2fa_form = false;
$qrCodeUrl      = '';
$smarty->assign('twofa_enabled', $twofa_enabled);

// Setup beginnen (QR anzeigen)
if (!$twofa_enabled && ($_POST['action'] ?? '') === 'start_2fa') {
    $tfa = new TwoFactorAuth('StudyHub');
    $secret = $tfa->createSecret();
    $_SESSION['2fa_secret_temp'] = $secret;

    $qrCodeUrl     = $tfa->getQRCodeImageAsDataUri($username, $secret);
    $show_2fa_form = true;
    $smarty->assign('qrCodeUrl', $qrCodeUrl);
    $smarty->assign('show_2fa_form', true);
}

// Setup bestätigen (Code-Eingabe prüfen)
if (!$twofa_enabled && ($_POST['action'] ?? '') === 'confirm_2fa') {
    $tfa = new TwoFactorAuth('StudyHub');

    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');

    if (strlen($code) !== 6) {
        $smarty->assign('flash', ['type' => 'danger', 'message' => 'Bitte gib einen gültigen 6-stelligen Code ein.']);
        $show_2fa_form = true;
        if (isset($_SESSION['2fa_secret_temp'])) {
            $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($username, $_SESSION['2fa_secret_temp']);
        }
        $smarty->assign('qrCodeUrl', $qrCodeUrl);
        $smarty->assign('show_2fa_form', true);
    } else {
        $secret = $_SESSION['2fa_secret_temp'] ?? null;

        if ($secret && $tfa->verifyCode($secret, $code)) {
            $encrypted = encryptData($secret);
            DbFunctions::storeTwoFASecret($username, $encrypted);

            unset($_SESSION['2fa_secret_temp']);
            $twofa_enabled = true;
            $smarty->assign('flash', ['type' => 'success', 'message' => '2FA wurde erfolgreich aktiviert.']);
            $smarty->assign('twofa_enabled', true);
        } else {
            $smarty->assign('flash', ['type' => 'danger', 'message' => 'Falscher Code. Bitte erneut versuchen.']);
            $show_2fa_form = true;
            $qrCodeUrl     = $tfa->getQRCodeImageAsDataUri($username, $secret);
            $smarty->assign('qrCodeUrl', $qrCodeUrl);
            $smarty->assign('show_2fa_form', true);
        }
    }
}

// 2FA deaktivieren
if ($twofa_enabled && ($_POST['action'] ?? '') === 'disable_2fa') {
    DbFunctions::disableTwoFA($username);
    unset($_SESSION['2fa_passed']);

    $twofa_enabled  = false;
    $show_2fa_form  = false;
    $qrCodeUrl      = '';
    $smarty->assign('flash', ['type' => 'success', 'message' => '2FA wurde deaktiviert.']);
    $smarty->assign('twofa_enabled', false);
    $smarty->assign('show_2fa_form', false);
}

$smarty->assign('show_2fa_form', $show_2fa_form);
$smarty->assign('qrCodeUrl', $qrCodeUrl);
