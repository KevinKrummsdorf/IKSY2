<?php
require_once __DIR__ . '/Database.php';

class CourseModel
{
    public static function getCourseIdByName(string $name): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM courses WHERE name = ?');
        $stmt->execute([$name]);
        $course = $stmt->fetch();
        if (!$course) {
            throw new RuntimeException("Kurs nicht gefunden: $name");
        }
        return (int)$course['id'];
    }

    public static function getOrCreateMaterial(int $courseId, string $title, string $description): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM materials WHERE course_id = ? AND title = ?');
        $stmt->execute([$courseId, $title]);
        $material = $stmt->fetch();
        if ($material) {
            return (int)$material['id'];
        }
        $stmt = $pdo->prepare('INSERT INTO materials (course_id, title, description) VALUES (?, ?, ?)');
        $stmt->execute([$courseId, $title, $description]);
        return (int)$pdo->lastInsertId();
    }
}
