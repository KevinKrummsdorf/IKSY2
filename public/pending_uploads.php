<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isMod   = ($_SESSION['role'] ?? '') === 'mod';

if (!$isAdmin && !$isMod) {
    $reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
    handle_error(403, $reason, 'both');
}

$db = new Database();
$uploadRepository = new UploadRepository($db);

$filters = [
    'title'       => trim($_GET['title'] ?? ''),
    'filename'    => trim($_GET['filename'] ?? ''),
    'course_name' => trim($_GET['course_name'] ?? ''),
    'username'    => trim($_GET['username'] ?? ''),
    'from_date'   => trim($_GET['from_date'] ?? ''),
    'to_date'     => trim($_GET['to_date'] ?? ''),
];

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadId = (int) ($_POST['upload_id'] ?? 0);
    $action   = $_POST['action'] ?? '';
    $note     = trim($_POST['note'] ?? '');

    $actionSuccess = false;

    try {
        if ($action === 'approve') {
            $uploadRepository->approveUpload($uploadId, (int)$_SESSION['user_id']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Upload wurde freigegeben.'];
            $actionSuccess = true;
        } elseif ($action === 'reject') {
            if ($note === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bitte einen Ablehnungsgrund angeben.'];
            } else {
                $uploadRepository->rejectUpload($uploadId, (int)$_SESSION['user_id'], $note);
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Upload wurde abgelehnt.'];
                $actionSuccess = true;
            }
        }

        // Mail an den Nutzer senden, falls Aktion erfolgreich war
        if ($actionSuccess) {
            $uploadData = $uploadRepository->getUploadDetails($uploadId);

            if ($uploadData && !empty($uploadData['email']) && !empty($uploadData['username'])) {
                $subject = ($action === 'approve')
                    ? 'Dein Upload auf StudyHub wurde freigegeben'
                    : 'Dein Upload auf StudyHub wurde abgelehnt';

                $body = ($action === 'approve')
                    ? "<p>Hallo {$uploadData['username']},</p>
                       <p>dein Upload <strong>{$uploadData['title']}</strong> im Kurs <strong>{$uploadData['course_name']}</strong> wurde von einem Moderator freigegeben.</p>
                       <p>Vielen Dank für deinen Beitrag!</p>
                       <p>Viele Grüße,<br>Dein StudyHub-Team</p>"
                    : "<p>Hallo {$uploadData['username']},</p>
                       <p>dein Upload <strong>{$uploadData['title']}</strong> im Kurs <strong>{$uploadData['course_name']}</strong> wurde leider abgelehnt.</p>
                       <p><strong>Grund:</strong> {$note}</p>
                       <p>Bitte überprüfe deinen Upload oder kontaktiere das Support-Team.</p>
                       <p>Viele Grüße,<br>Dein StudyHub-Team</p>";

                sendMail($uploadData['email'], $uploadData['username'], $subject, $body);
            }
        }
    } catch (Exception $e) {
        error_log("Fehler in pending_uploads.php: " . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ein Fehler ist aufgetreten.'];
    }

    header('Location: pending_uploads.php');
    exit;
}

$doExport = isset($_GET['export']) && $_GET['export'] === 'csv';

if ($doExport) {
    $allUploads = $uploadRepository->getFilteredPendingUploads($filters);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=pending_uploads.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Dateiname', 'Titel', 'Kurs', 'Uploader', 'Hochgeladen am']);
    foreach ($allUploads as $u) {
        fputcsv($out, [
            $u['stored_name'],
            $u['title'],
            $u['course_name'],
            $u['username'] ?? '',
            $u['uploaded_at'],
        ]);
    }
    fclose($out);
    exit;
}

$totalCount    = $uploadRepository->countFilteredPendingUploads($filters);
$totalPages    = (int)ceil($totalCount / $pageSize);
$pendingUploads = $uploadRepository->getFilteredPendingUploads($filters, $pageSize, $offset);

if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

$smarty->assign('pending_uploads', $pendingUploads);
$smarty->assign('currentPage', $currentPage);
$smarty->assign('totalPages', $totalPages);
$smarty->assign('filters', $filters);
$smarty->assign('isAdmin', $isAdmin);
$smarty->assign('isMod', $isMod);
$smarty->assign('username', $_SESSION['username'] ?? '');

$smarty->display('pending_uploads.tpl');
