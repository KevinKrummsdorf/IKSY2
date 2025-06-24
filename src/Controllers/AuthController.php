<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../includes/crypto.inc.php';

class AuthController
{
    public function login(string $identifier, string $password): array
    {
        $user = UserModel::findByIdentifier($identifier);
        if (!$user) {
            return ['success' => false, 'message' => 'Benutzer nicht gefunden'];
        }
        $userId = (int)$user['id'];
        if (UserModel::isAccountLocked($userId)) {
            return ['success' => false, 'message' => 'Account gesperrt'];
        }
        if ((int)$user['is_verified'] !== 1) {
            return ['success' => false, 'message' => 'Account nicht verifiziert'];
        }
        if (!verifyPassword($password, $user['password_hash'])) {
            UserModel::updateFailedAttempts($userId);
            $attempts = $this->getFailedAttempts($userId);
            if ($attempts >= 5) {
                UserModel::lockAccount($userId, 15);
            }
            return ['success' => false, 'message' => 'Falsches Passwort'];
        }
        UserModel::resetFailedAttempts($userId);
        UserModel::updateLastLogin($userId);
        return ['success' => true, 'user' => $user];
    }

    private function getFailedAttempts(int $userId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT failed_attempts FROM user_security WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
