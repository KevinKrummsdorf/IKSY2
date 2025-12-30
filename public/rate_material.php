<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');


// Sicherstellen, dass der Benutzer eingeloggt ist
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nicht eingeloggt']);
    exit;
}

// Eingaben validieren
$materialId = filter_input(INPUT_POST, 'material_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5]
]);

if (!$materialId || !$rating) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Eingaben']);
    exit;
}

// DB-Verbindung
$db = new Database();
$uploadRepository = new UploadRepository($db);
$userId = $_SESSION['user_id'];

try {
    // Prüfen ob Bewertung existiert
    $existingRating = $uploadRepository->getUserMaterialRating($materialId, $userId);
    
    if ($existingRating) {
        // Update vorhandener Bewertung
        $db->execute("UPDATE material_ratings SET rating = :rating WHERE material_id = :material_id AND user_id = :user_id", ['rating' => $rating, 'material_id' => $materialId, 'user_id' => $userId]);
    } else {
        // Neue Bewertung einfügen
        $db->execute("INSERT INTO material_ratings (material_id, user_id, rating) VALUES (:material_id, :user_id, :rating)", ['material_id' => $materialId, 'user_id' => $userId, 'rating' => $rating]);
    }
    
    $avg = $uploadRepository->getAverageMaterialRating($materialId);
    echo json_encode([
        'success' => true,
        'average_rating' => $avg['average_rating'] ?? 0,
        'total_ratings' => $avg['total_ratings'] ?? 0,
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Interner Fehler']);
}
