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

$error   = '';
$success = '';
$warning = '';

$action = $_POST['action'] ?? ($_GET['action'] ?? 'upload');
$action = $action === 'suggest' ? 'suggest' : 'upload';

$courses = DbFunctions::getAllCourses();
$userGroups = DbFunctions::fetchGroupsByUser((int)$_SESSION['user_id']);
$groupUpload = false;
$selectedGroupId = 0;
$uploadTarget = 'public';

if (isset($_GET['group_id'])) {
    $gid = (int)$_GET['group_id'];
    foreach ($userGroups as $g) {
        if ((int)$g['id'] === $gid) {
            $selectedGroupId = $gid;
            $groupUpload = true;
            $uploadTarget = 'group';
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $error = 'Ungültiger CSRF-Token.';
    } elseif ($action === 'suggest') {
        $courseSuggestion = trim($_POST['course_suggestion'] ?? '');
        $confirmSimilar   = isset($_POST['confirm_similar']);
        $smarty->assign('courseSuggestion', $courseSuggestion);

        if ($courseSuggestion === '') {
            $error = 'Bitte gib einen Kursnamen an.';
        } else {
            $similar = DbFunctions::findSimilarCourse($courseSuggestion);
            if ($similar !== null && !$confirmSimilar) {
                $warning = "Ein ähnlicher Kurs existiert bereits: '{$similar}'. Meintest du diesen? Klicke erneut auf 'Vorschlagen', um trotzdem einzureichen.";
            } else {
                try {
                    DbFunctions::submitCourseSuggestion($courseSuggestion, (int)$_SESSION['user_id']);
                                        $success = 'Kursvorschlag wurde eingereicht.';
                    $_POST = [];
                } catch (Exception $e) {
                    $error = 'Fehler beim Speichern des Kursvorschlags.';
                                    }
            }
        }
    } else {
        $title         = trim($_POST['title'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $course        = trim($_POST['course'] ?? '');
        $customCourse  = trim($_POST['custom_course'] ?? '');

        $uploadTarget  = $_POST['upload_target'] ?? 'public';
        if ($uploadTarget === 'group') {
            $selectedGroupId = (int)($_POST['group_id'] ?? 0);
            $ids = array_column($userGroups, 'id');
            if (in_array($selectedGroupId, $ids, true)) {
                $groupUpload = true;
            } else {
                $selectedGroupId = 0;
                $groupUpload = false;
            }
        }

        $smarty->assign('customCourse', $customCourse);
        $smarty->assign('uploadTarget', $uploadTarget);

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
            } elseif (!in_array($mimeType, $allowed, true)) {
                $error = 'Nur PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT und PPTX erlaubt.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Maximal 10 MB erlaubt.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Upload-Verzeichnis konnte nicht erstellt werden.';
                } else {
                    $originalName = basename($file['name']);
                    $safeName     = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
                    $ext          = pathinfo($safeName, PATHINFO_EXTENSION);

                    $prefix = $course === '__custom__' ? $customCourse : $course;
                    $prefix = strtolower(preg_replace('/[^a-z0-9]/i', '_', $prefix));
                    $prefix = trim(preg_replace('/_+/', '_', $prefix), '_');

                    $baseName       = $prefix . '_' . uniqid();
                    $tmpName        = $baseName . '.' . $ext;
                    $tmpPath        = $uploadDir . $tmpName;
                    $pdfName        = $baseName . '.pdf';
                    $pdfPath        = $uploadDir . $pdfName;

                    if (move_uploaded_file($file['tmp_name'], $tmpPath)) {
                        $storedName = $tmpName;
                        if (strtolower($ext) === 'pdf') {
                            if (rename($tmpPath, $pdfPath)) {
                                $storedName = $pdfName;
                            } else {
                                                            }
                        } else {
                            try {
                                $converted = convert_file_to_pdf($tmpPath, $pdfPath);
                                if ($converted && file_exists($pdfPath)) {
                                    unlink($tmpPath);
                                    $storedName = $pdfName;
                                } else {
                                                                    }
                            } catch (Exception $e) {
                                                            }
                        }
                        try {
                            if ($course === '__custom__') {
                                DbFunctions::submitCourseSuggestion($customCourse, (int)$_SESSION['user_id']);
                                $success = 'Kursvorschlag wurde eingereicht. Datei wird erst nach Freigabe akzeptiert.';
                            } else {
                                $courseId   = DbFunctions::getCourseIdByName($course);
                                $materialId = DbFunctions::getOrCreateMaterial($courseId, $title, $description);

                                if ($groupUpload) {
                                    $uploadId = DbFunctions::uploadFile(
                                        $storedName,
                                        $materialId,
                                        (int)$_SESSION['user_id'],
                                        $selectedGroupId,
                                        true
                                    );
                                    $success = 'Datei erfolgreich für die Lerngruppe hochgeladen.';
                                } else {
                                    $uploadId = DbFunctions::uploadFile($storedName, $materialId, (int)$_SESSION['user_id']);
                                    $success  = 'Datei erfolgreich hochgeladen und wartet auf Freigabe.';
                                }


                                                            }

                            $_POST = [];
                        } catch (Exception $e) {
                            $error = 'Fehler beim Speichern des Uploads.';
                        }
                    } else {
                        $error = 'Konnte Datei nicht speichern.';
                    }
                }
            }
        }
    }
}

$smarty->assign([
    'base_url'            => $config['base_url'],
    'app_name'            => $config['app_name'],
    'isLoggedIn'          => isset($_SESSION['user_id']),
    'username'            => $_SESSION['username'] ?? null,
    'courses'             => $courses,
    'userGroups'          => $userGroups,
    'selectedGroupId'     => $selectedGroupId,
    'uploadTarget'        => $uploadTarget,
    'selectedCourse'      => $_POST['course'] ?? '',
    'title'               => $_POST['title'] ?? '',
    'description'         => $_POST['description'] ?? '',
    'csrf_token'          => $_SESSION['csrf_token'],
    'action'              => $action,
    'courseSuggestion'    => $_POST['course_suggestion'] ?? '',
]);

if ($error) {
    $smarty->assign('error', $error);
}
if ($warning) {
    $smarty->assign('warning', $warning);
}
if ($success) {
    $smarty->assign('success', $success);
}

$smarty->display('upload.tpl');
