<?php
declare(strict_types=1);

/**
 * Holt Login-Logs, falls der Benutzer Admin ist.
 */
function fetchLoginLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $limit = max(1, min($limit, 1000));

    try {
        $stmt = $pdo->prepare('
            SELECT
                ll.user_id,
                u.username,
                ll.ip_address,
                ll.success,
                ll.reason,
                ll.created_at
            FROM login_logs AS ll
            LEFT JOIN users AS u ON ll.user_id = u.id
            ORDER BY ll.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (defined('DEBUG') && DEBUG) {
            error_log('Login-Log-Fehler: ' . $e->getMessage());
        }
        return [];
    }
}

/**
 * Holt Captcha-Logs, falls der Benutzer Admin ist.
 */
function fetchCaptchaLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $limit = max(1, min($limit, 1000));

    try {
        $stmt = $pdo->prepare('
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
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (defined('DEBUG') && DEBUG) {
            error_log('Captcha-Log-Fehler: ' . $e->getMessage());
        }
        return [];
    }
}
