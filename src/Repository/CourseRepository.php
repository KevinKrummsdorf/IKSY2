<?php

declare(strict_types=1);

class CourseRepository
{
    private Database $db;

    private array $courseSynonyms = [
        'mathe' => 'mathematik',
        'info'  => 'informatik',
        'iksy2' => 'iksy 2'
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllCourses(): array
    {
        return $this->db->execute("SELECT name AS value, name AS name FROM courses ORDER BY name ASC", [], true);
    }

    public function searchCourses(string $query): array
    {
        return $this->db->execute('SELECT name FROM courses WHERE name LIKE ? ORDER BY name LIMIT 10', [$query . '%'], true, PDO::FETCH_COLUMN);
    }

    public function getPendingCourseSuggestions(): array
    {
        $sql = "
            SELECT pcs.*, u.username
            FROM pending_course_suggestions pcs
            JOIN users u ON pcs.user_id = u.id
            WHERE pcs.is_approved IS NULL
            ORDER BY pcs.suggested_at DESC
        ";
        return $this->db->execute($sql, [], true);
    }

    public function getCourseIdByName(string $name): int
    {
        $course = $this->db->fetchOne("SELECT id FROM courses WHERE name = ?", [$name]);
        if (!$course) {
            throw new RuntimeException("Course not found: $name");
        }
        return (int)$course['id'];
    }

    public function getOrCreateMaterial(int $courseId, string $title, string $desc): int
    {
        $material = $this->db->fetchOne("SELECT id FROM materials WHERE course_id = ? AND title = ?", [$courseId, $title]);

        if ($material) {
            return (int)$material['id'];
        }

        $this->db->execute("INSERT INTO materials (course_id, title, description) VALUES (?, ?, ?)", [$courseId, $title, $desc]);
        return (int)$this->db->lastInsertId();
    }

    public function submitCourseSuggestion(string $courseName, int $userId): void
    {
        $this->db->execute("INSERT INTO pending_course_suggestions (course_name, user_id) VALUES (?, ?)", [$courseName, $userId]);
    }

    private function canonicalCourseName(string $name): string
    {
        $norm = strtolower(preg_replace('/\s+/', '', $name));
        if (isset($this->courseSynonyms[$norm])) {
            $norm = strtolower(preg_replace('/\s+/', '', $this->courseSynonyms[$norm]));
        }
        return $norm;
    }

    public function findSimilarCourse(string $courseName): ?string
    {
        $courses = $this->db->execute('SELECT name FROM courses', [], true, PDO::FETCH_COLUMN);
        $canonical = [];
        foreach ($courses as $course) {
            $canonical[$course] = $this->canonicalCourseName($course);
        }

        $target = $this->canonicalCourseName($courseName);

        foreach ($canonical as $orig => $norm) {
            if ($target === $norm) {
                return $orig;
            }
            if (levenshtein($target, $norm) <= 2) {
                return $orig;
            }
            if (soundex($target) === soundex($norm)) {
                return $orig;
            }
        }

        return null;
    }
    
    public function getFilteredCourseSuggestions(array $filters = []): array
    {
        $sql = "
            SELECT pcs.*, u.username, u.email
            FROM pending_course_suggestions pcs
            JOIN users u ON pcs.user_id = u.id
            WHERE pcs.is_approved IS NULL
        ";
        $params = [];

        if (!empty($filters['username'])) {
            $sql .= " AND u.username LIKE ?";
            $params[] = '%' . $filters['username'] . '%';
        }

        if (!empty($filters['course_name'])) {
            $sql .= " AND pcs.course_name LIKE ?";
            $params[] = '%' . $filters['course_name'] . '%';
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND pcs.suggested_at >= ?";
            $params[] = $filters['from_date'] . ' 00:00:00';
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND pcs.suggested_at <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }

        $sql .= " ORDER BY pcs.suggested_at DESC";

        return $this->db->execute($sql, $params, true);
    }
}
