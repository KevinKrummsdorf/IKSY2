<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.inc.php';

// Zugriff prüfen: POST und gültiger Token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'], $_POST['csrf_token'])) {
    $userId    = $_SESSION['user_id'];
    $csrfToken = $_POST['csrf_token'];
    
    // CSRF-Schutz
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(403);
        exit('Ungültiger CSRF-Token');
    }
    
    try {
        $pdo = DbFunctions::db_connect();
        
        // Bildnamen aus DB holen
        $stmt = $pdo->prepare("SELECT profile_picture FROM profile WHERE user_id = ?");
        $stmt->execute([$userId]);
        $filename = $stmt->fetchColumn();
        
        // Datei löschen, wenn vorhanden
        if ($filename) {
            $filepath = __DIR__ . '/../uploads/profile_pictures/' . basename($filename);
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        // DB-Eintrag zurücksetzen
        $stmt = $pdo->prepare("UPDATE profile SET profile_picture = NULL WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Zurück zur Bearbeitungsseite mit Erfolgsmeldung
        header("Location: edit_profile?img_deleted=1");
        exit;
        
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Beim Löschen des Profilbilds ist ein Fehler aufgetreten.');
    }
}

// Ungültiger Zugriff (z. B. direkter Aufruf)
http_response_code(403);
exit('Zugriff verweigert');
