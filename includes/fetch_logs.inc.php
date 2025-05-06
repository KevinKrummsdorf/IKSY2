<?php
declare(strict_types=1);

function fetchLoginLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $sql = '
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
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchCaptchaLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
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

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}