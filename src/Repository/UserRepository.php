<?php

declare(strict_types=1);

class UserRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function fetchVerificationUser(string $token): ?array
    {
        $sql = '
            SELECT u.id, u.username, uv.is_verified
            FROM verification_tokens vt
            JOIN users u ON u.id = vt.user_id
            JOIN user_verification uv ON u.id = uv.user_id
            WHERE vt.verification_token = :token
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':token' => $token]);
    }

    public function verifyUser(int $userId): int
    {
        $sql = 'UPDATE user_verification SET is_verified = TRUE WHERE user_id = :id';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function unverifyUser(int $userId): int
    {
        $sql = 'UPDATE user_verification SET is_verified = FALSE WHERE user_id = :id';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function deleteVerificationToken(int $userId): int
    {
        $sql = 'DELETE FROM verification_tokens WHERE user_id = :id';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function insertUser(string $username, string $email, string $passwordHash): int
    {
        $this->db->beginTransaction();
        try {
            $sql = '
                INSERT INTO users (username, email, password_hash)
                VALUES (:u, :e, :p)
            ';
            $this->db->execute($sql, [
                ':u' => $username,
                ':e' => $email,
                ':p' => $passwordHash,
            ]);

            $userId = (int)$this->db->lastInsertId();

            $this->db->execute('INSERT INTO user_verification (user_id) VALUES (:uid)', [':uid' => $userId]);
            $this->db->execute('INSERT INTO user_security (user_id) VALUES (:uid)', [':uid' => $userId]);
            $this->db->execute('INSERT INTO user_2fa (user_id) VALUES (:uid)', [':uid' => $userId]);

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function assignRole(int $userId, int $roleId): int
    {
        $sql = '
            INSERT INTO user_roles (user_id, role_id)
            VALUES (:uid, :rid)
        ';
        return $this->db->execute($sql, [
            ':uid' => $userId,
            ':rid' => $roleId,
        ]);
    }

    public function fetchUserByIdentifier(string $input): ?array
    {
        $sql = '
            SELECT
                u.id,
                u.username,
                u.email,
                u.password_hash,
                uv.is_verified,
                r.role_name AS role
            FROM users u
            JOIN user_verification uv ON u.id = uv.user_id
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.username = :identUser OR u.email = :identEmail
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [
            ':identUser'  => $input,
            ':identEmail' => $input,
        ]);
    }

    public function updateLastLogin(int $userId): int
    {
        $sql = 'UPDATE users SET last_login = NOW() WHERE id = :id';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function storeTwoFASecret(string $username, string $encryptedSecret): void
    {
        $userId = $this->db->fetchValue('SELECT id FROM users WHERE username = :u', [':u' => $username]);
        $sql = '
            UPDATE user_2fa
            SET twofa_secret = :secret, is_twofa_enabled = 1
            WHERE user_id = :id
        ';
        $this->db->execute($sql, [
            ':secret' => $encryptedSecret,
            ':id'     => $userId,
        ]);
    }

    public function getTwoFASecret(string $username): ?string
    {
        $sql = '
            SELECT u2fa.twofa_secret
            FROM users u
            JOIN user_2fa u2fa ON u.id = u2fa.user_id
            WHERE u.username = :username AND u2fa.is_twofa_enabled = 1
        ';
        return $this->db->fetchValue($sql, [':username' => $username]);
    }

    public function isTwoFAEnabled(string $username): bool
    {
        $sql = '
            SELECT u2fa.is_twofa_enabled
            FROM users u
            JOIN user_2fa u2fa ON u.id = u2fa.user_id
            WHERE u.username = :username
        ';
        return (bool) $this->db->fetchValue($sql, [':username' => $username]);
    }

    public function disableTwoFA(string $username): void
    {
        $userId = $this->db->fetchValue('SELECT id FROM users WHERE username = :u', [':u' => $username]);
        $sql = '
            UPDATE user_2fa
            SET twofa_secret = NULL, is_twofa_enabled = 0
            WHERE user_id = :id
        ';
        $this->db->execute($sql, [':id' => $userId]);
    }

    public function updateFailedAttempts(int $userId, int $incrementBy = 1): int
    {
        $sql = '
            UPDATE user_security
            SET failed_attempts = failed_attempts + :inc
            WHERE user_id = :id
        ';
        return $this->db->execute($sql, [
            ':inc' => $incrementBy,
            ':id'  => $userId,
        ]);
    }

    public function lockAccount(int $userId, int $lockMinutes = 15): int
    {
        $sql = '
            UPDATE user_security
            SET account_locked = true
            WHERE user_id = :id
        ';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function resetFailedAttempts(int $userId): int
    {
        $sql = '
            UPDATE user_security
            SET failed_attempts = 0, account_locked = false
            WHERE user_id = :id
        ';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function isAccountLocked(int $userId): bool
    {
        $sql = '
            SELECT account_locked
            FROM user_security
            WHERE user_id = :id
        ';
        $locked = $this->db->fetchValue($sql, [':id' => $userId]);
        return (bool)$locked;
    }

    public function unlockAccount(int $userId): int
    {
        $sql = '
            UPDATE user_security
            SET account_locked = 0, failed_attempts = 0
            WHERE user_id = :user_id
        ';
        return $this->db->execute($sql, [':user_id' => $userId]);
    }

    public function getUserById(int $userId): ?array
    {
        $sql = 'SELECT username, email FROM users WHERE id = :id';
        return $this->db->fetchOne($sql, [':id' => $userId]);
    }

    public function fetchUserById(int $userId): ?array
    {
        $sql = '
            SELECT
                u.id,
                u.username,
                u.email,
                u.password_hash,
                uv.is_verified,
                r.role_name AS role
            FROM users u
            JOIN user_verification uv ON u.id = uv.user_id
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.id = :userId
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':userId' => $userId]);
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE username = :u';
        $params = [':u' => $username];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        return (bool) $this->db->fetchValue($sql, $params);
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :e';
        $params = [':e' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        return (bool) $this->db->fetchValue($sql, $params);
    }

    public function storePasswordResetToken(int $userId, string $token, int $expiresMinutes = 60): void
    {
        $sql = '
            INSERT INTO password_reset_tokens (user_id, reset_token, expires_at)
            VALUES (:uid, :token, NOW() + MAKE_INTERVAL(mins => :exp))
            ON CONFLICT (user_id) DO UPDATE SET
                reset_token = EXCLUDED.reset_token,
                expires_at = EXCLUDED.expires_at
        ';
        $this->db->execute($sql, [
            ':uid'  => $userId,
            ':token'=> $token,
            ':exp'  => $expiresMinutes,
        ]);
    }

    public function fetchPasswordResetUser(string $token): ?array
    {
        $sql = '
            SELECT u.id, u.username, u.email, u.password_hash
            FROM password_reset_tokens pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.reset_token = :token AND pr.expires_at > NOW()
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':token' => $token]);
    }

    public function deletePasswordResetToken(int $userId): int
    {
        $sql = 'DELETE FROM password_reset_tokens WHERE user_id = :id';
        return $this->db->execute($sql, [':id' => $userId]);
    }

    public function updatePassword(int $userId, string $passwordHash): int
    {
        $sql = 'UPDATE users SET password_hash = :pw WHERE id = :id';
        return $this->db->execute($sql, [':pw' => $passwordHash, ':id' => $userId]);
    }

    public function updateEmail(int $userId, string $email): int
    {
        $sql = 'UPDATE users SET email = :email WHERE id = :id';
        return $this->db->execute($sql, [':email' => $email, ':id' => $userId]);
    }

    public function updateUsername(int $userId, string $username): int
    {
        $sql = 'UPDATE users SET username = :username WHERE id = :id';
        return $this->db->execute($sql, [':username' => $username, ':id' => $userId]);
    }

    public function searchUsers(string $searchTerm): array
    {
        $sql = "
            SELECT id, username AS result_username
            FROM users
            WHERE username LIKE :term1 OR email LIKE :term2
        ";
        return $this->db->execute($sql, [
            ':term1' => '%' . $searchTerm . '%',
            ':term2' => '%' . $searchTerm . '%'
        ], true);
    }
}
