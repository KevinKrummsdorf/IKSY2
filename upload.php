<?php
session_start();
// 1. Login-Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// 2. Konfiguration (DB-Zugang)
require_once 'config.php'; // definiert DSN, DB_USER, DB_PASS

// 3. Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course      = trim($_POST['course']);

    // Pflichtfelder prüfen
    if (empty($title) || !isset($_FILES['file'])) {
        $error = 'Titel und Datei sind erforderlich.';
    } else {
        // 4. Datei-Validierung
        $allowed = ['application/pdf','image/jpeg','image/png'];
        $file    = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Fehler beim Upload.';
        } elseif (!in_array($file['type'], $allowed)) {
            $error = 'Nur PDF, JPG und PNG erlaubt.';
        } elseif ($file['size'] > 10*1024*1024) {
            $error = 'Maximal 10 MB erlaubt.';
        } else {
            // 5. Dateisystem: uploads/-Verzeichnis
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            // 6. Eindeutiger Dateiname
            $ext           = pathinfo($file['name'], PATHINFO_EXTENSION);
            $storedName    = uniqid('mat_') . '.' . $ext;
            $destination   = $uploadDir . $storedName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // 7. Speicherung in DB
                try {
                    $pdo = new PDO(DSN, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    $stmt = $pdo->prepare(
                      'INSERT INTO uploads 
                        (user_id,title,description,course,original_filename,stored_filename,filetype,filesize)
                       VALUES 
                        (?,?,?,?,?,?,?,?)'
                    );
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $title,
                        $description,
                        $course,
                        $file['name'],
                        $storedName,
                        $file['type'],
                        $file['size']
                    ]);
                    $success = 'Datei erfolgreich hochgeladen.';
                } catch (PDOException $e) {
                    $error = 'Datenbank-Fehler: '.$e->getMessage();
                }
            } else {
                $error = 'Konnte Datei nicht speichern.';
            }
        }
    }
}