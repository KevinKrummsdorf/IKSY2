<?php
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/password_reset.inc.php';
require_once __DIR__ . '/../src/Repository/UserRepository.php';

/**
 * Stellt Funktionen zum Zurücksetzen und Ändern von Passwörtern bereit.
 */
class PasswordController
{
    private Database $db;
    private UserRepository $userRepository;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->userRepository = new UserRepository($db);
    }

    /**
     * Startet den Reset-Prozess und versendet eine E-Mail mit Token.
     */
    public function requestReset(string $identifier): void
    {
        $user = $this->userRepository->fetchUserByIdentifier($identifier);
        if (!$user) {
            throw new RuntimeException('Benutzer nicht gefunden');
        }

        $token = bin2hex(random_bytes(32));
        $this->userRepository->storePasswordResetToken((int)$user['id'], $token);
        sendPasswordResetEmail($this->db, (int)$user['id'], $user['username'], $user['email'], $token);
    }

    /**
     * Setzt das Passwort anhand eines gültigen Tokens zurück.
     */
    public function resetPassword(string $token, string $password): void
    {
        $user = $this->userRepository->fetchPasswordResetUser($token);
        if (!$user) {
            throw new RuntimeException('Token ungültig oder abgelaufen');
        }
        if (!password_meets_requirements($password)) {
            throw new RuntimeException('Passwort erfüllt nicht die Bedingungen');
        }
        $hash = hashPassword($password);
        $this->userRepository->updatePassword((int)$user['id'], $hash);
        $this->userRepository->deletePasswordResetToken((int)$user['id']);
        sendPasswordResetSuccessEmail($this->db, (int)$user['id'], $user['username'], $user['email']);
    }

    /**
     * Ändert das Passwort eines angemeldeten Benutzers.
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = $this->userRepository->fetchUserById($userId);
        if (!$user || !verifyPassword($oldPassword, $user['password_hash'])) {
            throw new RuntimeException('Aktuelles Passwort falsch');
        }
        if (!password_meets_requirements($newPassword)) {
            throw new RuntimeException('Passwort erfüllt nicht die Bedingungen');
        }
        $hash = hashPassword($newPassword);
        $this->userRepository->updatePassword($userId, $hash);
    }
}
