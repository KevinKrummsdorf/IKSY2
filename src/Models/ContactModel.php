<?php
require_once __DIR__ . '/Database.php';

class ContactModel
{
    public static function saveRequest(string $contactId, string $name, string $email, string $subject, string $message, string $ip, ?string $ua): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO contact_requests (contact_id, name, email, subject, message, ip_address, user_agent) VALUES (:cid, :name, :email, :subject, :message, :ip, :ua)');
        $stmt->execute([
            ':cid'     => $contactId,
            ':name'    => $name,
            ':email'   => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':ip'      => $ip,
            ':ua'      => $ua,
        ]);
    }
}
