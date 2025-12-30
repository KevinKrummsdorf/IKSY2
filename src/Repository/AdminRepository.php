<?php

declare(strict_types=1);

class AdminRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function fetchCaptchaLogs(bool $isAdmin, int $limit = 50): array
    {
        if (!$isAdmin) {
            return [];
        }

        $sql = '
            SELECT
                id,
                token,
                success,
                score,
                action,
                hostname,
                error_reason,
                created_at
            FROM captcha_log
            ORDER BY created_at DESC
            LIMIT :limit
        ';
        return $this->db->execute($sql, [':limit' => $limit], true);
    }

    public function fetchLockedUsers(bool $isAdmin, int $limit = 50): array
    {
        if (!$isAdmin) {
            return [];
        }

        $sql = '
            SELECT u.id, u.username, u.email, us.failed_attempts
            FROM users u
            JOIN user_security us ON u.id = us.user_id
            WHERE us.account_locked = TRUE
            ORDER BY us.failed_attempts DESC
            LIMIT :limit
        ';
        return $this->db->execute($sql, [':limit' => $limit], true);
    }

    public function countCaptchaLogs(): int
    {
        return (int)$this->db->fetchValue('SELECT COUNT(*) FROM captcha_log');
    }

    public function getCaptchaLogsPage(int $limit, int $offset): array
    {
        $sql = '
            SELECT id, token, success, score, action, hostname, error_reason, created_at
            FROM captcha_log
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';
        return $this->db->execute($sql, [':limit' => $limit, ':offset' => $offset], true);
    }

    public function countLockedUsers(): int
    {
        return (int)$this->db->fetchValue('SELECT COUNT(*) FROM user_security WHERE account_locked = TRUE');
    }

    public function getLockedUsersPage(int $limit, int $offset): array
    {
        $sql = '
            SELECT u.id, u.username, u.email, us.failed_attempts
            FROM users u
            JOIN user_security us ON u.id = us.user_id
            WHERE us.account_locked = TRUE
            ORDER BY us.failed_attempts DESC
            LIMIT :limit OFFSET :offset
        ';
        return $this->db->execute($sql, [':limit' => $limit, ':offset' => $offset], true);
    }

    public function getAllLockedUsers(): array
    {
        $sql = '
            SELECT u.id, u.username, u.email, us.failed_attempts
            FROM users u
            JOIN user_security us ON u.id = us.user_id
            WHERE us.account_locked = TRUE
            ORDER BY us.failed_attempts DESC
        ';
        return $this->db->execute($sql, [], true);
    }

    public function countFilteredCaptchaLogs(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM captcha_log WHERE 1=1";
        $params = [];
        $sql .= $this->applyCaptchaFilters($filters, $params);
        return (int)$this->db->fetchValue($sql, $params);
    }

    public function getFilteredCaptchaLogs(array $filters = [], ?int $limit = null, ?int $offset = null, bool $includeToken = false): array
    {
        $columns = $includeToken
            ? "token, success, score, action, hostname, error_reason, created_at"
            : "success, score, action, hostname, error_reason, created_at";
        $sql = "SELECT {$columns} FROM captcha_log WHERE 1=1";
        $params = [];
        $sql .= $this->applyCaptchaFilters($filters, $params);
        $sql .= " ORDER BY created_at DESC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        return $this->db->execute($sql, $params, true);
    }

    private function applyCaptchaFilters(array $filters, array &$params): string
    {
        $sql = '';
        if ($filters['success'] !== '') {
            $sql .= " AND success = ?";
            $params[] = (int)$filters['success'];
        }
        if ($filters['action'] !== '') {
            $sql .= " AND action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        if ($filters['hostname'] !== '') {
            $sql .= " AND hostname LIKE ?";
            $params[] = '%' . $filters['hostname'] . '%';
        }
        if ($filters['score_min'] !== '') {
            $sql .= " AND score >= ?";
            $params[] = (float)$filters['score_min'];
        }
        if ($filters['score_max'] !== '') {
            $sql .= " AND score <= ?";
            $params[] = (float)$filters['score_max'];
        }
        if ($filters['from_date'] !== '') {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['from_date'] . ' 00:00:00';
        }
        if ($filters['to_date'] !== '') {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }
        return $sql;
    }

    public function getFilteredLockedUsers(array $filters = []): array
    {
        $sql = "
            SELECT u.id, u.username, u.email, us.failed_attempts
            FROM users u
            JOIN user_security us ON u.id = us.user_id
            WHERE us.account_locked = 1
        ";
        $params = [];

        if (!empty($filters['username'])) {
            $sql .= " AND u.username LIKE ?";
            $params[] = '%' . $filters['username'] . '%';
        }

        if ($filters['min_attempts'] !== '') {
            $sql .= " AND us.failed_attempts >= ?";
            $params[] = (int)$filters['min_attempts'];
        }

        if ($filters['max_attempts'] !== '') {
            $sql .= " AND us.failed_attempts <= ?";
            $params[] = (int)$filters['max_attempts'];
        }

        $sql .= " ORDER BY us.failed_attempts DESC";

        return $this->db->execute($sql, $params, true);
    }
}
