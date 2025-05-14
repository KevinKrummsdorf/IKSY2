<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.inc.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$monolog = getLogger('upload');
$log     = new MonologLoggerAdapter($monolog);

$error   = '';
$success = '';

$courses = [
    ['value' => 'mathe',     'name' => 'Mathematik'],
    ['value' => 'statistik', 'name' => 'Statistik'],
    ['value' => 'englisch',  'name' => 'Englisch'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF-Check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $error = 'Ungültiger CSRF-Token.';
        $log->error('CSRF-Token ungültig', ['user_id' => $_SESSION['user_id']]);
    } else {
        // Eingaben holen
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $course      = trim($_POST['course'] ?? '');

        // Pflichtfelder prüfen
        if ($title === '' || !isset($_FILES['file'])) {
            $error = 'Titel und Datei sind erforderlich.';
        } elseif (!in_array($course, array_column($courses, 'value'), true)) {
            $error = 'Ungültiger Kurs ausgewählt.';
        } else {
            $file     = $_FILES['file'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowed  = ['application/pdf','image/jpeg','image/png','text/plain'];

            // Upload-Fehler prüfen
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Fehler beim Datei-Upload.';
                $log->error('Datei-Upload-Fehler', [
                    'user_id' => $_SESSION['user_id'],
                    'error'   => $file['error'],
                ]);
            } elseif (!in_array($mimeType, $allowed, true)) {
                $error = 'Nur PDF, JPG, PNG und TXT erlaubt.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Maximal 10 MB erlaubt.';
            } else {
                // Zielverzeichnis vorbereiten
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Upload-Verzeichnis konnte nicht erstellt werden.';
                    $log->error('Upload-Verzeichnis konnte nicht erstellt werden', [
                        'user_id' => $_SESSION['user_id'],
                    ]);
                } else {
                    // Dateinamen sanitizen und eindeutig machen
                    $originalName = basename($file['name']);
                    $safeName     = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
                    $ext          = pathinfo($safeName, PATHINFO_EXTENSION);
                    $storedName   = uniqid('mat_') . '.' . $ext;
                    $destination  = $uploadDir . $storedName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        try {
                            // Eintrag in Haupttabelle
                            DbFunctions::insertUpload($storedName, $title, $description, $course);
                            $log->info('Datei erfolgreich hochgeladen', [
                                'user_id'     => $_SESSION['user_id'],
                                'stored_name' => $storedName,
                                'title'       => $title,
                                'description' => $description,
                                'course'      => $course,
                            ]);

                            // Eintrag in Log-Tabelle
                            DbFunctions::insertUploadLog((int)$_SESSION['user_id'], $storedName);
                            $log->info('Upload-Log erfolgreich gespeichert', [
                                'user_id'     => $_SESSION['user_id'],
                                'stored_name' => $storedName,
                            ]);

                            $success = 'Datei erfolgreich hochgeladen.';
                            // Formular zurücksetzen
                            $_POST = [];
                        } catch (Exception $e) {
                            $error = 'Datenbankfehler beim Speichern.';
                            $log->error('DB-Error', [
                                'user_id' => $_SESSION['user_id'],
                                'msg'     => $e->getMessage(),
                            ]);
                        }
                    } else {
                        $error = 'Konnte Datei nicht speichern.';
                        $log->error('Datei konnte nicht gespeichert werden', [
                            'user_id' => $_SESSION['user_id'],
                        ]);
                    }
                }
            }
        }
    }
}

// Smarty-Template befüllen und anzeigen
$smarty->assign([
    'base_url'       => $config['base_url'],
    'app_name'       => $config['app_name'],
    'isLoggedIn'     => isset($_SESSION['user_id']),
    'username'       => $_SESSION['username'] ?? null,
    'courses'        => $courses,
    'selectedCourse' => $_POST['course'] ?? '',
    'title'          => $_POST['title'] ?? '',
    'description'    => $_POST['description'] ?? '',
    'csrf_token'     => $_SESSION['csrf_token'],
]);

if ($error) {
    $smarty->assign('error', $error);
}

if ($success) {
    $smarty->assign('success', $success);
}

$smarty->display('upload.tpl');
