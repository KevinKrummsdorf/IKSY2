<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/2fa.inc.php';

use RobThree\Auth\TwoFactorAuth;

$username = $_SESSION['2fa_user'] ?? null;
$userId   = $_SESSION['user_id_pending'] ?? null;
$role     = $_SESSION['role_pending'] ?? 'user';
$ip       = getClientIp();
$maskedIp = maskIp($ip);


if (!$username || !$userId) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');

    try {
        // 🔐 Secret entschlüsseln & extrahieren
        $secret = null;
        $encrypted = DbFunctions::getTwoFASecret($username);

        if ($encrypted) {
            try {
                $decrypted = decryptData($encrypted);
                if ($decrypted instanceof \ParagonIE\HiddenString\HiddenString) {
                    $secret = $decrypted->getString(); // Kein Objekt mehr
                }
            } catch (Throwable $e) {
                throw new RuntimeException('Entschlüsselung fehlgeschlagen: ' . $e->getMessage());
            }
        }

        if (empty($secret)) {
            throw new RuntimeException('2FA-Secret konnte nicht geladen werden.');
        }

        // TOTP prüfen
        $tfa = new TwoFactorAuth('StudyHub');

        if ($tfa->verifyCode($secret, $code)) {
            // Erfolgreich eingeloggt
            $_SESSION['user_id']       = $userId;
            $_SESSION['username']      = $username;
            $_SESSION['role']          = $role;
            $_SESSION['2fa_passed']    = true;
            $_SESSION['last_activity'] = time();

            unset($_SESSION['2fa_user'], $_SESSION['user_id_pending'], $_SESSION['role_pending']);

            DbFunctions::insertLoginLog((int)$userId, $maskedIp, true);

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => '2FA erfolgreich. Willkommen zurück!',
            ];

            session_write_close();
            header('Location: dashboard');
            exit;
        } else {
            DbFunctions::insertLoginLog((int)$userId, $maskedIp, false, 'wrong_2fa_code');

            $smarty->assign('message', 'Falscher Code. Bitte erneut eingeben.');
        }
    } catch (Throwable $e) {
            'error' => $e->getMessage()
        ]);
        $smarty->assign('message', 'Fehler beim Verifizieren des 2FA-Codes: ' . $e->getMessage());
    }
}

$smarty->assign('username', $username);

if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

$smarty->display('2fa_login.tpl');
