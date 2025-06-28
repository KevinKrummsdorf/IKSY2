<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriff prüfen: POST und gültiger Token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'], $_POST['csrf_token'])) {
    $userId    = $_SESSION['user_id'];
    $csrfToken = $_POST['csrf_token'];
    
    // CSRF-Schutz
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $reason = 'Ungültiger CSRF-Token';
        handle_error(403, $reason, 'both');
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
        $reason = 'Beim Löschen des Profilbilds ist ein Fehler aufgetreten.';
        handle_error(500, $reason);
    }
}

// Ungültiger Zugriff (z. B. direkter Aufruf)
$reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
handle_error(403, $reason, 'both');
