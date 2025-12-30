<?php

declare(strict_types=1);

class UploadRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getApprovedUploads(): array
    {
        $query = '
            SELECT id, stored_name, material_id, uploaded_by
            FROM uploads
            WHERE is_approved = 1 AND group_id IS NULL
        ';
        return $this->db->execute($query, [], true);
    }

    public function getAllMaterials(): array
    {
        $query = '
            SELECT DISTINCT m.id, m.title, m.description, c.name AS course_name
            FROM materials m
            JOIN uploads u ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE u.is_approved = 1 AND u.group_id IS NULL
        ';
        return $this->db->execute($query, [], true);
    }

    public function getMaterialsByTitle(string $searchTerm): array
    {
        $query = '
            SELECT DISTINCT m.id, m.title, m.description, c.name AS course_name
            FROM materials m
            JOIN uploads u ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE u.is_approved = 1 AND u.group_id IS NULL AND m.title LIKE :search
        ';
        return $this->db->execute($query, ['search' => '%' . $searchTerm . '%'], true);
    }

    public function getAverageMaterialRating(int $materialId): ?array
    {
        $sql = 'SELECT AVG(rating) AS average_rating, COUNT(*) AS total_ratings FROM material_ratings WHERE material_id = :material_id';
        return $this->db->fetchOne($sql, ['material_id' => $materialId]);
    }

    public function getUserMaterialRating(int $materialId, int $userId): ?array
    {
        $sql = 'SELECT rating FROM material_ratings WHERE material_id = :material_id AND user_id = :user_id';
        return $this->db->fetchOne($sql, ['material_id' => $materialId, 'user_id' => $userId]);
    }

    public function getAverageRatingsForMaterials(array $materialIds): array
    {
        if (empty($materialIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($materialIds), '?'));
        $sql = "
            SELECT material_id, AVG(rating) AS average_rating, COUNT(*) AS total_ratings
            FROM material_ratings
            WHERE material_id IN ($placeholders)
            GROUP BY material_id
        ";
        return $this->db->execute($sql, array_values($materialIds), true);
    }

    public function getUserRatingsForMaterials(array $materialIds, int $userId): array
    {
        if (empty($materialIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($materialIds), '?'));
        $sql = "
            SELECT material_id, rating
            FROM material_ratings
            WHERE user_id = ? AND material_id IN ($placeholders)
        ";
        $params = array_merge([$userId], array_values($materialIds));
        return $this->db->execute($sql, $params, true);
    }

    public function insertUpload(string $storedName, string $title, string $description, string $course): int
    {
        $sql = '
            INSERT INTO uploads
               (stored_name, title, description, course)
            VALUES
               (:stored_name, :title, :description, :course)
        ';
        return $this->db->execute($sql, [
            ':stored_name' => $storedName,
            ':title'       => $title,
            ':description' => $description,
            ':course'      => $course,
        ]);
    }

    public function uploadFile(string $storedName, int $materialId, int $userId, ?int $groupId = null, bool $autoApprove = false): int
    {
        $sql = "
            INSERT INTO uploads (stored_name, material_id, uploaded_by, uploaded_at, is_approved, group_id)
            VALUES (:storedName, :materialId, :userId, NOW(), :approved, :groupId)
        ";
        $this->db->execute($sql, [
            ':storedName' => $storedName,
            ':materialId' => $materialId,
            ':userId'     => $userId,
            ':approved'   => $autoApprove ? 1 : 0,
            ':groupId'    => $groupId,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function approveUpload(int $uploadId, int $adminId): bool
    {
        $upload = $this->db->fetchOne("
            SELECT u.*, m.title, m.description, m.course_id
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            WHERE u.id = ?
        ", [$uploadId]);

        if (!$upload) {
            throw new RuntimeException("Upload $uploadId not found or incomplete.");
        }

        $existing = $this->db->fetchOne("
            SELECT id FROM materials
            WHERE course_id = ? AND title = ?
        ", [$upload['course_id'], $upload['title']]);

        if (!$existing) {
            $this->db->execute("
                INSERT INTO materials (course_id, title, description)
                VALUES (?, ?, ?)
            ", [$upload['course_id'], $upload['title'], $upload['description'] ?? null]);

            $newMaterialId = (int)$this->db->lastInsertId();
            $this->db->execute("UPDATE uploads SET material_id = ? WHERE id = ?", [$newMaterialId, $uploadId]);
        }

        $this->db->execute("UPDATE uploads SET is_approved = 1 WHERE id = ?", [$uploadId]);

        return true;
    }

    public function rejectUpload(int $uploadId, int $modId, ?string $note = null): bool
    {
        if ($note === null || trim($note) === '') {
            throw new InvalidArgumentException('Rejection reason required');
        }

        $this->db->execute("UPDATE uploads SET is_rejected = 1 WHERE id = ?", [$uploadId]);
        return true;
    }

    public function getPendingUploads(): array
    {
        $sql = "
            SELECT u.*, us.username, m.title, m.description, c.name AS course_name
            FROM uploads u
            LEFT JOIN users us ON u.uploaded_by = us.id
            LEFT JOIN materials m ON u.material_id = m.id
            LEFT JOIN courses c ON m.course_id = c.id
            WHERE u.is_approved = 0 AND u.is_rejected = 0
            ORDER BY u.uploaded_at DESC
        ";
        return $this->db->execute($sql, [], true);
    }

    public function getUploadDetails(int $uploadId): ?array
    {
        $sql = "
            SELECT u.id, u.stored_name, u.uploaded_at,
                   us.username, us.email,
                   m.title, c.name AS course_name
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            LEFT JOIN users us ON u.uploaded_by = us.id
            WHERE u.id = ?
        ";
        return $this->db->fetchOne($sql, [$uploadId]);
    }

    public function getApprovedUploadById(int $uploadId): ?array
    {
        $sql = "
            SELECT stored_name
            FROM uploads
            WHERE id = ? AND is_approved = 1
            LIMIT 1
        ";
        return $this->db->fetchOne($sql, [$uploadId]);
    }

    public function getApprovedUploadsByUser(int $userId): array
    {
        $sql = "
            SELECT u.id, u.stored_name, u.uploaded_at, m.title, c.name AS course_name
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE u.uploaded_by = ?
              AND u.is_approved = 1
            ORDER BY u.uploaded_at DESC
        ";
        return $this->db->execute($sql, [$userId], true);
    }

    public function getFilteredUploadsByUser(int $userId, array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $sql = "
            SELECT u.id, u.stored_name, u.uploaded_at,
                   u.is_approved, u.is_rejected,
                   m.title, c.name AS course_name
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE u.uploaded_by = ?
        ";
        $params = [$userId];
        $sql .= $this->applyFilters($filters, $params);
        $sql .= " ORDER BY u.uploaded_at ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        return $this->db->execute($sql, $params, true);
    }

    public function countFilteredUploadsByUser(int $userId, array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE u.uploaded_by = ?
        ";
        $params = [$userId];
        $sql .= $this->applyFilters($filters, $params);

        return (int) $this->db->fetchValue($sql, $params);
    }

    public function deleteUpload(int $uploadId, int $userId): ?string
    {
        $this->db->beginTransaction();
        try {
            $row = $this->db->fetchOne('SELECT stored_name, material_id FROM uploads WHERE id = ? AND uploaded_by = ?', [$uploadId, $userId]);

            if (!$row) {
                $this->db->rollBack();
                return null;
            }

            $this->db->execute('DELETE FROM uploads WHERE id = ? AND uploaded_by = ?', [$uploadId, $userId]);
            $this->cleanupMaterial((int)$row['material_id']);

            $this->db->commit();
            return $row['stored_name'];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteGroupUpload(int $uploadId, int $groupId, int $adminId): ?string
    {
        $this->db->beginTransaction();
        try {
            $row = $this->db->fetchOne('SELECT stored_name, material_id FROM uploads WHERE id = ? AND group_id = ?', [$uploadId, $groupId]);

            if (!$row) {
                $this->db->rollBack();
                return null;
            }

            $this->db->execute('DELETE FROM uploads WHERE id = ? AND group_id = ?', [$uploadId, $groupId]);
            $this->cleanupMaterial((int)$row['material_id']);

            $this->db->commit();
            return $row['stored_name'];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function cleanupMaterial(int $materialId): void
    {
        $count = $this->db->fetchValue('SELECT COUNT(*) FROM uploads WHERE material_id = ?', [$materialId]);
        if ((int)$count === 0) {
            $this->db->execute('DELETE FROM materials WHERE id = ?', [$materialId]);
        }
    }

    private function applyFilters(array $filters, array &$params): string
    {
        $sql = '';
        if (!empty($filters['title'])) {
            $sql .= " AND m.title LIKE ?";
            $params[] = '%' . $filters['title'] . '%';
        }
        if (!empty($filters['filename'])) {
            $sql .= " AND u.stored_name LIKE ?";
            $params[] = '%' . $filters['filename'] . '%';
        }
        if (!empty($filters['course_name'])) {
            $sql .= " AND c.name LIKE ?";
            $params[] = '%' . $filters['course_name'] . '%';
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND u.uploaded_at >= ?";
            $params[] = $filters['from_date'] . ' 00:00:00';
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND u.uploaded_at <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }
        return $sql;
    }

    public function getUserUploadSuggestions(int $userId): array
    {
        $titles = $this->db->execute(
            'SELECT DISTINCT m.title FROM uploads u JOIN materials m ON u.material_id = m.id WHERE u.uploaded_by = ?',
            [$userId],
            true,
            PDO::FETCH_COLUMN
        );

        $filenames = $this->db->execute(
            'SELECT DISTINCT stored_name FROM uploads WHERE uploaded_by = ?',
            [$userId],
            true,
            PDO::FETCH_COLUMN
        );

        $courses = $this->db->execute(
            'SELECT DISTINCT c.name FROM uploads u JOIN materials m ON u.material_id = m.id JOIN courses c ON m.course_id = c.id WHERE u.uploaded_by = ?',
            [$userId],
            true,
            PDO::FETCH_COLUMN
        );

        return [
            'titles'       => $titles,
            'filenames'    => $filenames,
            'course_names' => $courses,
        ];
    }
}
