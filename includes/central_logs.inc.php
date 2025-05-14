<?php
declare(strict_types=1);

/**
 * Zentrale Log-Funktionen für Admin-Panel
 *
 * Beinhaltet Login-Logs, Captcha-Logs, Kontaktanfragen und Upload-Logs.
 */

/**
 * === Login-Logs ===
 */
 function fetchLoginLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $limit = max(1, min($limit, 1000));

    try {
        $stmt = $pdo->prepare(
            'SELECT ll.user_id, u.username, ll.ip_address, ll.success, ll.reason, ll.created_at
             FROM login_logs AS ll
             LEFT JOIN users AS u ON ll.user_id = u.id
             ORDER BY ll.created_at DESC
             LIMIT :limit'
        );
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

function countLoginLogs(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_logs');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getLoginLogsPage(PDO $pdo, int $limit, int $offset, bool $isAdmin): array
{
    if (!$isAdmin) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT ll.user_id, u.username, ll.ip_address, ll.success, ll.reason, ll.created_at
         FROM login_logs AS ll
         LEFT JOIN users AS u ON ll.user_id = u.id
         ORDER BY ll.created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * === Captcha-Logs ===
 */
function fetchCaptchaLogs(PDO $pdo, bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $limit = max(1, min($limit, 1000));

    try {
        $stmt = $pdo->prepare(
            'SELECT id, token, success, score, action, hostname, error_reason, created_at
             FROM captcha_log
             ORDER BY created_at DESC
             LIMIT :limit'
        );
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

function countCaptchaLogs(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM captcha_log');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getCaptchaLogsPage(PDO $pdo, int $limit, int $offset): array
{
    $stmt = $pdo->prepare(
        'SELECT id, token, success, score, action, hostname, error_reason, created_at
         FROM captcha_log
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * === Kontaktanfragen ===
 */
function getRecentContactRequests(PDO $pdo, int $limit = 100): array
{
    $stmt = $pdo->prepare(
        'SELECT contact_id, name, email, subject, created_at
         FROM contact_requests
         ORDER BY created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteContactRequest(PDO $pdo, string $contactId): void
{
    $stmt = $pdo->prepare(
        'DELETE FROM contact_requests WHERE contact_id = :id'
    );
    $stmt->bindValue(':id', $contactId, PDO::PARAM_STR);
    $stmt->execute();
}

function countContactRequests(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM contact_requests');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getContactRequestsPage(PDO $pdo, int $limit, int $offset): array
{
    $stmt = $pdo->prepare(
        'SELECT contact_id, name, email, subject, created_at
         FROM contact_requests
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * === Upload-Logs ===
 */
function fetchUploadLogs(PDO $pdo, bool $isAdmin, bool $isMod, int $limit = 50): array
{
    if (!$isAdmin && !$isMod) {
        return [];
    }

    $limit = max(1, min($limit, 1000));

    try {
        $stmt = $pdo->prepare(
            'SELECT id, user_id, stored_name, uploaded_at
             FROM upload_logs
             ORDER BY uploaded_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (defined('DEBUG') && DEBUG) {
            error_log('Upload-Log-Fehler: ' . $e->getMessage());
        }
        return [];
    }
}

function countUploadLogs(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM upload_logs');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getUploadLogsPage(PDO $pdo, int $limit, int $offset): array
{
    $stmt = $pdo->prepare(
        'SELECT id, user_id, stored_name, uploaded_at
         FROM upload_logs
         ORDER BY uploaded_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
