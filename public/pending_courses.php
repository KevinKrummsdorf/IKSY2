<?php
declare(strict_types=1);
// Session wird bereits in config.inc.php gestartet

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}
if (!in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    $reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
    handle_error(403, $reason, 'both');
}

$filters = [
    'username'    => trim($_GET['username'] ?? ''),
    'course_name' => trim($_GET['course_name'] ?? ''),
    'from_date'   => trim($_GET['from_date'] ?? ''),
    'to_date'     => trim($_GET['to_date'] ?? '')
];

$pdo = DbFunctions::db_connect();

// Aktion: Genehmigung oder Ablehnung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int) ($_POST['suggestion_id'] ?? 0);
    $action   = $_POST['action'] ?? '';
    $reason   = trim($_POST['rejection_reason'] ?? '');

    $stmt = $pdo->prepare("
        SELECT pcs.course_name, u.username, u.email
        FROM pending_course_suggestions pcs
        JOIN users u ON pcs.user_id = u.id
        WHERE pcs.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        if ($action === 'approve') {
            $pdo->beginTransaction();

            $pdo->prepare("INSERT INTO courses (name) VALUES (?) ON CONFLICT (name) DO NOTHING")
                ->execute([$row['course_name']]);
            $pdo->prepare("UPDATE pending_course_suggestions SET is_approved = 1 WHERE id = ?")
                ->execute([$id]);

            $pdo->commit();

            sendMail(
                $row['email'], $row['username'],
                'Kursvorschlag angenommen',
                "<p>Hallo {$row['username']},</p>
                 <p>dein Kursvorschlag <strong>{$row['course_name']}</strong> wurde genehmigt und steht nun zur Verfügung.</p>
                 <p>Danke für deinen Beitrag!</p>
                 <p>Viele Grüße,<br>Dein StudyHub-Team</p>"
            );

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vorschlag genehmigt.'];
        } elseif ($action === 'reject') {
            if ($reason === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bitte eine Begründung angeben.'];
            } else {
                $pdo->prepare("UPDATE pending_course_suggestions SET is_approved = 0 WHERE id = ?")
                    ->execute([$id]);

                sendMail(
                    $row['email'], $row['username'],
                    'Kursvorschlag abgelehnt',
                    "<p>Hallo {$row['username']},</p>
                     <p>dein Kursvorschlag <strong>{$row['course_name']}</strong> wurde leider abgelehnt.</p>
                     <p><strong>Begründung:</strong> {$reason}</p>
                     <p>Bei Fragen wende dich bitte an den Support.<br>
                     <p>Viele Grüße,<br>Dein StudyHub-Team</p>"
                );

                $_SESSION['flash'] = ['type' => 'info', 'message' => 'Vorschlag abgelehnt.'];
            }
        }
    }

    header('Location: pending_courses.php');
    exit;
}

// CSV-Export
$entries = DbFunctions::getFilteredCourseSuggestions($filters);
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=course_suggestions.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Kursname', 'Benutzer', 'E-Mail', 'Vorschlagsdatum']);
    foreach ($entries as $e) {
        fputcsv($out, [$e['course_name'], $e['username'], $e['email'], $e['suggested_at']]);
    }
    fclose($out);
    exit;
}

$smarty->assign([
    'pending_courses' => $entries,
    'filters'         => $filters,
    'username'        => $_SESSION['username'] ?? '',
    'isAdmin'         => $_SESSION['role'] === 'admin',
    'isMod'           => $_SESSION['role'] === 'mod',
    'flash'           => $_SESSION['flash'] ?? null,
]);
unset($_SESSION['flash']);

$smarty->display('pending_courses.tpl');
