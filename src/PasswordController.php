<?php
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/password_reset.inc.php';

/**
 * Stellt Funktionen zum Zurücksetzen und Ändern von Passwörtern bereit.
 */
class PasswordController
{
    /**
     * Startet den Reset-Prozess und versendet eine E-Mail mit Token.
     */
    public static function requestReset(string $identifier): void
    {
        $user = DbFunctions::fetchUserByIdentifier($identifier);
        if (!$user) {
            throw new RuntimeException('Benutzer nicht gefunden');
        }

        $token = bin2hex(random_bytes(32));
        DbFunctions::storePasswordResetToken((int)$user['id'], $token);
        sendPasswordResetEmail(DbFunctions::db_connect(), (int)$user['id'], $user['username'], $user['email'], $token);
    }

    /**
     * Setzt das Passwort anhand eines gültigen Tokens zurück.
     */
    public static function resetPassword(string $token, string $password): void
    {
        $user = DbFunctions::fetchPasswordResetUser($token);
        if (!$user) {
            throw new RuntimeException('Token ungültig oder abgelaufen');
        }
        if (!password_meets_requirements($password)) {
            throw new RuntimeException('Passwort erfüllt nicht die Bedingungen');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        DbFunctions::updatePassword((int)$user['id'], $hash);
        DbFunctions::deletePasswordResetToken((int)$user['id']);
        sendPasswordResetSuccessEmail(DbFunctions::db_connect(), (int)$user['id'], $user['username'], $user['email']);
    }

    /**
     * Ändert das Passwort eines angemeldeten Benutzers.
     */
    public static function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = DbFunctions::fetchUserById($userId);
        if (!$user || !verifyPassword($oldPassword, $user['password_hash'])) {
            throw new RuntimeException('Aktuelles Passwort falsch');
        }
        if (!password_meets_requirements($newPassword)) {
            throw new RuntimeException('Passwort erfüllt nicht die Bedingungen');
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        DbFunctions::updatePassword($userId, $hash);
    }
}
