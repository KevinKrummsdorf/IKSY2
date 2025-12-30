<?php
require_once __DIR__ . '/../../../includes/config.inc.php';
require_once __DIR__ . '/../../../src/Database.php';
require_once __DIR__ . '/../../../src/Repository/CourseRepository.php';

header('Content-Type: application/json');

$query = trim($_GET['query'] ?? '');
if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $courseRepository = new CourseRepository($db);
    $names = $courseRepository->searchCourses($query);
    echo json_encode($names);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}
