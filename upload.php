<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use Smarty\Smarty;

// 1. Konfiguration laden (DB-Zugang & App-Settings)
$config = require_once __DIR__ . '/../includes/config.inc.php';

// 2. Smarty initialisieren
$smarty = new Smarty();
$smarty->setTemplateDir([
    __DIR__ . '/../templates/',
    __DIR__ . '/../templates/layouts/',
    __DIR__ . '/../templates/partials/',
]);
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// 3. CSRF-Token generieren (GET) bzw. prüfen (POST)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 4. Login-Check
//if (!isset($_SESSION['user_id'])) {
//    header('Location: login.php');
//    exit; }

$error   = '';
$success = '';

// 5. Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF-Validierung
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Ungültiger CSRF-Token.';
    } else {
        // Form-Daten
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $course      = trim($_POST['course'] ?? '');
        
        // Kurs-Whitelist
        $courses = [
            ['value' => 'mathe',     'name' => 'Mathematik'],
            ['value' => 'statistik', 'name' => 'Statistik'],
            ['value' => 'englisch',  'name' => 'Englisch'],
        ];
        
        // Pflichtfelder prüfen
        if ($title === '' || !isset($_FILES['file'])) {
            $error = 'Titel und Datei sind erforderlich.';
        } elseif (!in_array($course, array_column($courses, 'value'), true)) {
            $error = 'Ungültiger Kurs ausgewählt.';
        } else {
            $file = $_FILES['file'];
            
            // MIME-Type-Check serverseitig
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowed  = ['application/pdf', 'image/jpeg', 'image/png'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Fehler beim Datei-Upload.';
            } elseif (!in_array($mimeType, $allowed, true)) {
                $error = 'Nur PDF, JPG und PNG erlaubt.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Maximal 10 MB erlaubt.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Upload-Verzeichnis konnte nicht erstellt werden.';
                } else {
                    // Original-Namen sanitizen
                    $originalName = basename($file['name']);
                    $originalName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $originalName);
                    
                    // Eindeutigen Namen erzeugen
                    $ext        = pathinfo($originalName, PATHINFO_EXTENSION);
                    $storedName = uniqid('mat_') . '.' . $ext;
                    $destination = $uploadDir . $storedName;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // In DB speichern
                        try {
                            $pdo = new PDO(DSN, DB_USER, DB_PASS, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            ]);
                            $stmt = $pdo->prepare(
                                'INSERT INTO uploads
                                (user_id, title, description, course, original_filename, stored_filename, filetype, filesize)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                                );
                            $stmt->execute([
                                $_SESSION['user_id'],
                                $title,
                                $description,
                                $course,
                                $originalName,
                                $storedName,
                                $mimeType,
                                $file['size'],
                            ]);
                            $success = 'Datei erfolgreich hochgeladen.';
                            // Formular zurücksetzen
                            $_POST = [];
                        } catch (PDOException $e) {
                            error_log($e->getMessage());
                            $error = 'Datenbankfehler. Bitte Administrator benachrichtigen.';
                        }
                    } else {
                        $error = 'Konnte Datei nicht speichern.';
                    }
                }
            }
        }
    }
}

// 6. Template-Variablen zuweisen
$smarty->assign([
    'base_url'       => $config['base_url'],
    'app_name'       => $config['app_name'],
    'isLoggedIn'     => isset($_SESSION['user_id']),
    'username'       => $_SESSION['username'] ?? null,
    'courses'        => $courses ?? [],
    'selectedCourse' => $_POST['course'] ?? '',
    'title'          => $_POST['title'] ?? '',
    'description'    => $_POST['description'] ?? '',
    'csrf_token'     => $_SESSION['csrf_token'],
]);
if ($error)   $smarty->assign('error',   $error);
if ($success) $smarty->assign('success', $success);

// 7. Template anzeigen
$smarty->display('upload.tpl');
