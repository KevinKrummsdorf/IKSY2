<?php
declare(strict_types=1);

/**
 * Gibt die letzten Kontaktanfragen zurück (admin-only)
 */
function getRecentContactRequests(PDO $pdo, int $limit = 100): array {
    $stmt = $pdo->prepare('
        SELECT contact_id, name, email, subject, created_at
        FROM contact_requests
        ORDER BY created_at DESC
        LIMIT :limit
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteContactRequest(PDO $pdo, string $contactId): void
{
    $stmt = $pdo->prepare('
        DELETE FROM contact_requests
        WHERE contact_id = :id
    ');
    // Als STRING binden!
    $stmt->bindValue(':id', $contactId, PDO::PARAM_STR);
    $stmt->execute();
}
