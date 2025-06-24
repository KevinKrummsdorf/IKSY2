<?php
require_once __DIR__ . '/Database.php';

class UploadModel
{
    /**
     * Return all courses in the system as name/value pairs
     */
    public static function getAllCourses(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT name AS value, name AS name FROM courses ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Insert a new upload record and return its ID
     */
    public static function uploadFile(string $storedName, int $materialId, int $userId, ?int $groupId = null, bool $autoApprove = false): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO uploads (stored_name, material_id, uploaded_by, uploaded_at, is_approved, group_id)'
            . ' VALUES (:stored, :material, :user, NOW(), :approved, :gid)'
        );
        $stmt->execute([
            ':stored'   => $storedName,
            ':material' => $materialId,
            ':user'     => $userId,
            ':approved' => $autoApprove ? 1 : 0,
            ':gid'      => $groupId,
        ]);
        return (int)$pdo->lastInsertId();
    }
}
