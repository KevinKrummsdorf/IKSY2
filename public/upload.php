<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/csrf.inc.php';
require_once __DIR__ . '/../includes/pdf_utils.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/CourseRepository.php';
require_once __DIR__ . '/../src/Repository/GroupRepository.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';


if (empty($_SESSION['user_id'])) {
    $reason = "Du musst eingeloggt sein, um Dateien hochladen zu können.";
    handle_error(401, $reason, 'both');
}

$db = new Database();
$courseRepository = new CourseRepository($db);
$groupRepository = new GroupRepository($db);
$uploadRepository = new UploadRepository($db);

$error   = '';
$success = '';
$warning = '';

$action = $_POST['action'] ?? ($_GET['action'] ?? 'upload');
$action = $action === 'suggest' ? 'suggest' : 'upload';

$courses         = $courseRepository->getAllCourses();
$userGroups      = $groupRepository->fetchGroupsByUser((int)$_SESSION['user_id']);
$groupUpload     = false;
$selectedGroupId = 0;
$uploadTarget    = 'public';

if (isset($_GET['group_id'])) {
    $gid = (int)$_GET['group_id'];
    foreach ($userGroups as $g) {
        if ((int) $g['id'] === $gid) {
            $selectedGroupId = $gid;
            $groupUpload     = true;
            $uploadTarget    = 'group';
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();
    if ($action === 'suggest') {
        $courseSuggestion = strip_tags(trim($_POST['course_suggestion'] ?? ''));
        $confirmSimilar   = isset($_POST['confirm_similar']);
        $smarty->assign('courseSuggestion', $courseSuggestion);

        if (empty($courseSuggestion)) {
            $error = 'Bitte gib einen Kursnamen an.';
        } elseif (mb_strlen($courseSuggestion) > 100) {
            $error = 'Kursvorschlag darf nicht länger als 100 Zeichen sein.';
        } else {
            $similar = $courseRepository->findSimilarCourse($courseSuggestion);

            if ($similar !== null && ! $confirmSimilar) {
                $warning = "Ein ähnlicher Kurs existiert bereits: '{$similar}'. Meintest du diesen? Klicke erneut auf 'Vorschlagen', um trotzdem einzureichen.";
            } else {
                try {
                    $courseRepository->submitCourseSuggestion($courseSuggestion, (int) $_SESSION['user_id']);
                    $success = 'Kursvorschlag wurde eingereicht.';
                    $_POST   = [];
                } catch (Exception $e) {
                    $error = 'Fehler beim Speichern des Kursvorschlags.';
                }
            }
        }
    } else {
        $title        = strip_tags(trim($_POST['title'] ?? ''));
        $description  = strip_tags(trim($_POST['description'] ?? ''));
        $course       = trim($_POST['course'] ?? '');
        $customCourse = strip_tags(trim($_POST['custom_course'] ?? ''));

        $uploadTarget = $_POST['upload_target'] ?? 'public';
        if ($uploadTarget === 'group') {
            $selectedGroupId = (int) ($_POST['group_id'] ?? 0);
            $ids             = array_column($userGroups, 'id');
            $groupUpload     = in_array($selectedGroupId, $ids, true);

            if (! $groupUpload) {
                $selectedGroupId = 0;
            }
        }

        $smarty->assign('customCourse', $customCourse);
        $smarty->assign('uploadTarget', $uploadTarget);

        if (empty($title) || ! isset($_FILES['file'])) {
            $error = 'Titel und Datei sind erforderlich.';
        } elseif (mb_strlen($title) > 255) {
            $error = 'Titel darf nicht länger als 255 Zeichen sein.';
        } elseif (mb_strlen($description) > 1000) {
            $error = 'Beschreibung darf nicht länger als 1000 Zeichen sein.';
        } elseif ($course !== '__custom__' && ! in_array($course, array_column($courses, 'value'), true)) {
            $error = 'Ungültiger Kurs ausgewählt.';
        } elseif ($course === '__custom__' && empty($customCourse)) {
            $error = 'Bitte gib einen Kursvorschlag an.';
        } elseif ($course === '__custom__' && mb_strlen($customCourse) > 100) {
            $error = 'Benutzerdefinierter Kurs darf nicht länger als 100 Zeichen sein.';
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
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Fehler beim Datei-Upload.';
            } elseif (! in_array($mimeType, $allowed, true)) {
                $error = 'Nur PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT und PPTX erlaubt.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Maximal 10 MB erlaubt.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/';
                if (! is_dir($uploadDir) && ! mkdir($uploadDir, 0755, true)) {
                    $error = 'Upload-Verzeichnis konnte nicht erstellt werden.';
                } else {
                    $originalName = basename($file['name']);
                    $safeName     = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
                    $ext          = pathinfo($safeName, PATHINFO_EXTENSION);

                    $prefix = $course === '__custom__' ? $customCourse : $course;
                    $prefix = strtolower(preg_replace('/[^a-z0-9]/i', '_', $prefix));
                    $prefix = trim(preg_replace('/_+/', '_', $prefix), '_');

                    $baseName = $prefix . '_' . uniqid();
                    $tmpName  = $baseName . '.' . $ext;
                    $tmpPath  = $uploadDir . $tmpName;
                    $pdfName  = $baseName . '.pdf';
                    $pdfPath  = $uploadDir . $pdfName;

                    if (move_uploaded_file($file['tmp_name'], $tmpPath)) {
                        $storedName = $tmpName;

                        if (strtolower($ext) === 'pdf') {
                            if (rename($tmpPath, $pdfPath)) {
                                $storedName = $pdfName;
                            }
                        } else {
                            try {
                                $converted = convert_file_to_pdf($tmpPath, $pdfPath);
                                if ($converted && file_exists($pdfPath)) {
                                    unlink($tmpPath);
                                    $storedName = $pdfName;
                                }
                            } catch (Exception $e) {
                                // Konvertierung schlug fehl – Original behalten
                            }
                        }

                        try {
                            if ($course === '__custom__') {
                                $courseRepository->submitCourseSuggestion($customCourse, (int) $_SESSION['user_id']);
                                $success = 'Kursvorschlag wurde eingereicht. Datei wird erst nach Freigabe akzeptiert.';
                            } else {
                                $courseId   = $courseRepository->getCourseIdByName($course);
                                $materialId = $courseRepository->getOrCreateMaterial($courseId, $title, $description);

                                if ($groupUpload) {
                                    $uploadRepository->uploadFile(
                                        $storedName,
                                        $materialId,
                                        (int) $_SESSION['user_id'],
                                        $selectedGroupId,
                                        true
                                    );
                                    $success = 'Datei erfolgreich für die Lerngruppe hochgeladen.';
                                } else {
                                    $uploadRepository->uploadFile(
                                        $storedName,
                                        $materialId,
                                        (int) $_SESSION['user_id']
                                    );
                                    $success = 'Datei erfolgreich hochgeladen und wartet auf Freigabe.';
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
    'base_url'         => $config['base_url'],
    'app_name'         => $config['app_name'],
    'isLoggedIn'       => isset($_SESSION['user_id']),
    'username'         => $_SESSION['username'] ?? null,
    'courses'          => $courses,
    'userGroups'       => $userGroups,
    'selectedGroupId'  => $selectedGroupId,
    'uploadTarget'     => $uploadTarget,
    'selectedCourse'   => $_POST['course'] ?? '',
    'title'            => $_POST['title'] ?? '',
    'description'      => $_POST['description'] ?? '',
    'action'           => $action,
    'courseSuggestion' => $_POST['course_suggestion'] ?? '',
]);

if ($error)   { $smarty->assign('error',   $error); }
if ($warning) { $smarty->assign('warning', $warning); }
if ($success) { $smarty->assign('success', $success); }

$smarty->display('upload.tpl');
