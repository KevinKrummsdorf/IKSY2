<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/pdf_utils.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um Dateien hochladen zu können.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$log = LoggerFactory::get('upload');
$error   = '';
$success = '';

$action = $_POST['action'] ?? ($_GET['action'] ?? 'upload');
$action = $action === 'suggest' ? 'suggest' : 'upload';

$courses = DbFunctions::getAllCourses();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $error = 'Ungültiger CSRF-Token.';
        $log->error('CSRF-Token ungültig', ['user_id' => $_SESSION['user_id']]);
    } elseif ($action === 'suggest') {
        $courseSuggestion = trim($_POST['course_suggestion'] ?? '');
        $smarty->assign('courseSuggestion', $courseSuggestion);

        if ($courseSuggestion === '') {
            $error = 'Bitte gib einen Kursnamen an.';
        } else {
            try {
                DbFunctions::submitCourseSuggestion($courseSuggestion, (int)$_SESSION['user_id']);
                $log->info('Kursvorschlag eingereicht', [
                    'user_id'         => $_SESSION['user_id'],
                    'course_suggested'=> $courseSuggestion,
                ]);
                $success = 'Kursvorschlag wurde eingereicht.';
                $_POST = [];
            } catch (Exception $e) {
                $error = 'Fehler beim Speichern des Kursvorschlags.';
                $log->error('Kursvorschlag-Fehler', ['msg' => $e->getMessage()]);
            }
        }
    } else {
        $title         = trim($_POST['title'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $course        = trim($_POST['course'] ?? '');
        $customCourse  = trim($_POST['custom_course'] ?? '');

        $smarty->assign('customCourse', $customCourse);

        if ($title === '' || !isset($_FILES['file'])) {
            $error = 'Titel und Datei sind erforderlich.';
        } elseif ($course !== '__custom__' && !in_array($course, array_column($courses, 'value'), true)) {
            $error = 'Ungültiger Kurs ausgewählt.';
        } elseif ($course === '__custom__' && $customCourse === '') {
            $error = 'Bitte gib einen Kursvorschlag an.';
        } else {
            $file     = $_FILES['file'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowed  = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'text/plain',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.oasis.opendocument.text',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Fehler beim Datei-Upload.';
                $log->error('Datei-Upload-Fehler', ['user_id' => $_SESSION['user_id'], 'error' => $file['error']]);
            } elseif (!in_array($mimeType, $allowed, true)) {
                $error = 'Nur PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT und PPTX erlaubt.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Maximal 10 MB erlaubt.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Upload-Verzeichnis konnte nicht erstellt werden.';
                    $log->error('Upload-Verzeichnis fehlgeschlagen', ['user_id' => $_SESSION['user_id']]);
                } else {
                    $originalName = basename($file['name']);
                    $safeName     = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
                    $ext          = pathinfo($safeName, PATHINFO_EXTENSION);

                    // 🎯 Kurs-Name als Prefix
                    $prefix = $course === '__custom__' ? $customCourse : $course;
                    $prefix = strtolower(preg_replace('/[^a-z0-9]/i', '_', $prefix));
                    $prefix = trim(preg_replace('/_+/', '_', $prefix), '_');

                    $baseName      = $prefix . '_' . uniqid();
                    $storedNameTmp = $baseName . '.' . $ext;
                    $destination   = $uploadDir . $storedNameTmp;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $pdfName = $baseName . '.pdf';
                        $pdfPath = $uploadDir . $pdfName;
                        try {
                            convert_file_to_pdf($destination, $pdfPath);
                            unlink($destination);
                            $storedName = $pdfName;
                        } catch (Exception $e) {
                            $log->error('PDF-Konvertierung fehlgeschlagen', ['msg' => $e->getMessage()]);
                            $storedName = $storedNameTmp;
                        }
                        try {
                            if ($course === '__custom__') {
                                DbFunctions::submitCourseSuggestion($customCourse, (int)$_SESSION['user_id']);
                                $log->info('Kursvorschlag eingereicht', [
                                    'user_id'         => $_SESSION['user_id'],
                                    'course_suggested'=> $customCourse,
                                    'stored_name'     => $storedName,
                                ]);
                                $success = 'Kursvorschlag wurde eingereicht. Datei wird erst nach Freigabe akzeptiert.';
                            } else {
                                $courseId    = DbFunctions::getCourseIdByName($course);
                                $materialId  = DbFunctions::getOrCreateMaterial($courseId, $title, $description);
                                $uploadId    = DbFunctions::uploadFile($storedName, $materialId, (int)$_SESSION['user_id']);
                                DbFunctions::insertUploadLog((int)$_SESSION['user_id'], $uploadId);

                                $log->info('Upload erfolgreich', [
                                    'user_id'     => $_SESSION['user_id'],
                                    'upload_id'   => $uploadId,
                                    'stored_name' => $storedName,
                                    'material_id' => $materialId
                                ]);

                                $success = 'Datei erfolgreich hochgeladen und wartet auf Freigabe.';
                            }

                            $_POST = [];
                        } catch (Exception $e) {
                            $error = 'Fehler beim Speichern des Uploads.';
                            $log->error('Upload-Fehler', ['msg' => $e->getMessage()]);
                        }
                    } else {
                        $error = 'Konnte Datei nicht speichern.';
                        $log->error('Datei konnte nicht gespeichert werden', ['user_id' => $_SESSION['user_id']]);
                    }
                }
            }
        }
    }
}

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
    'action'         => $action,
    'courseSuggestion' => $_POST['course_suggestion'] ?? '',
]);

if ($error) {
    $smarty->assign('error', $error);
}
if ($success) {
    $smarty->assign('success', $success);
}

$smarty->display('upload.tpl');

