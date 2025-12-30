<?php

declare(strict_types=1);

class ContactRequestRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getRecentContactRequests(int $limit = 100): array
    {
        $sql = '
            SELECT contact_id, name, email, subject, created_at
            FROM contact_requests
            ORDER BY created_at DESC
            LIMIT :limit
        ';
        return $this->db->execute($sql, [':limit' => $limit], true);
    }

    public function deleteContactRequest(string $contactId): void
    {
        $sql = '
            DELETE FROM contact_requests
            WHERE contact_id = :id
        ';
        $this->db->execute($sql, [':id' => $contactId]);
    }

    public function countContactRequests(): int
    {
        return (int)$this->db->fetchValue('SELECT COUNT(*) FROM contact_requests');
    }

    public function getContactRequestsPage(int $limit, int $offset): array
    {
        $sql = '
            SELECT contact_id, name, email, subject, created_at
            FROM contact_requests
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';
        return $this->db->execute($sql, [':limit' => $limit, ':offset' => $offset], true);
    }

    public function getFilteredContactRequests(array $filters = []): array
    {
        $sql = "SELECT * FROM contact_requests WHERE 1=1";
        $params = [];

        if (!empty($filters['contact_id'])) {
            $sql .= " AND contact_id = ?";
            $params[] = $filters['contact_id'];
        }

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['email'])) {
            $sql .= " AND email LIKE ?";
            $params[] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['subject'])) {
            $sql .= " AND subject LIKE ?";
            $params[] = '%' . $filters['subject'] . '%';
        }

        if (!empty($filters['from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->execute($sql, $params, true);
    }

    public function createContactRequest(string $contactId, string $name, string $email, string $subject, string $message, ?string $ip, ?string $ua): void
    {
        $sql = 'INSERT INTO contact_requests
                (contact_id, name, email, subject, message, ip_address, user_agent)
            VALUES
                (:contact_id, :name, :email, :subject, :message, :ip, :ua)';
        $this->db->execute($sql, [
            ':contact_id' => $contactId,
            ':name'       => $name,
            ':email'      => $email,
            ':subject'    => $subject,
            ':message'    => $message,
            ':ip'         => $ip,
            ':ua'         => $ua,
        ]);
    }
}
