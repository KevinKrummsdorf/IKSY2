<?php
require_once __DIR__ . '/Database.php';

class UserModel
{
    public static function findByIdentifier(string $identifier): ?array
    {
        $pdo = Database::getConnection();
        $sql = 'SELECT u.id, u.username, u.email, u.password_hash, uv.is_verified,
                       r.role_name AS role
                FROM users u
                JOIN user_verification uv ON u.id = uv.user_id
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.username = :identUser OR u.email = :identEmail
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':identUser' => $identifier, ':identEmail' => $identifier]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function isAccountLocked(int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT account_locked FROM user_security WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
        return (bool)($stmt->fetchColumn());
    }

    public static function updateFailedAttempts(int $userId, int $inc = 1): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE user_security SET failed_attempts = failed_attempts + :inc WHERE user_id = :id');
        $stmt->execute([':inc' => $inc, ':id' => $userId]);
    }

    public static function lockAccount(int $userId, int $minutes = 15): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE user_security SET account_locked = 1 WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public static function resetFailedAttempts(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE user_security SET failed_attempts = 0, account_locked = 0 WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public static function updateLastLogin(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public static function isTwoFAEnabled(string $username): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT u2fa.is_twofa_enabled FROM users u JOIN user_2fa u2fa ON u.id = u2fa.user_id WHERE u.username = :name');
        $stmt->execute([':name' => $username]);
        return (bool)$stmt->fetchColumn();
    }
}
