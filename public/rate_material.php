<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.inc.php';
// Session wird bereits in config.inc.php gestartet

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
$pdo = DbFunctions::db_connect();
$userId = $_SESSION['user_id'];

try {
    // Prüfen ob Bewertung existiert
    $stmt = $pdo->prepare("SELECT id FROM material_ratings WHERE material_id = :material_id AND user_id = :user_id");
    $stmt->execute(['material_id' => $materialId, 'user_id' => $userId]);
    
    if ($stmt->fetch()) {
        // Update vorhandener Bewertung
        $update = $pdo->prepare("UPDATE material_ratings SET rating = :rating WHERE material_id = :material_id AND user_id = :user_id");
        $update->execute(['rating' => $rating, 'material_id' => $materialId, 'user_id' => $userId]);
    } else {
        // Neue Bewertung einfügen
        $insert = $pdo->prepare("INSERT INTO material_ratings (material_id, user_id, rating) VALUES (:material_id, :user_id, :rating)");
        $insert->execute(['material_id' => $materialId, 'user_id' => $userId, 'rating' => $rating]);
    }
    
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Interner Fehler']);
}
