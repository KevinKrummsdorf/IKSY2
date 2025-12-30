<?php
declare(strict_types=1);
// Session wird bereits in config.inc.php gestartet

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/AdminRepository.php';
require_once __DIR__ . '/../src/Repository/UserRepository.php';

// Zugriff nur für Admins oder Moderatoren
if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}
if (!in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    $reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
    handle_error(403, $reason, 'both');
}

$db = new Database();
$adminRepository = new AdminRepository($db);
$userRepository = new UserRepository($db);

// Benutzer entsperren + Mail versenden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_user_id'])) {
    $unlockId = (int)$_POST['unlock_user_id'];
    $userRepository->unlockAccount($unlockId);

    // Benutzerinformationen abrufen
    $user = $userRepository->getUserById($unlockId);
    if ($user && !empty($user['email'])) {
        $subject = 'Dein Account bei StudyHub wurde entsperrt';
        $body = "<p>Hallo {$user['username']},</p>
                 <p>dein Account bei StudyHub wurde von einem Administrator entsperrt. Du kannst dich ab sofort wieder anmelden.</p>
                 <p>Viele Grüße,<br>Dein StudyHub-Team</p>";
        try {
            sendMail($user['email'], $user['username'], $subject, $body);
        } catch (Exception $e) {
            error_log('[Entsperr-Mail] Fehler: ' . $e->getMessage());
        }
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => "Benutzer #{$unlockId} wurde entsperrt und benachrichtigt."];
    header('Location: locked_users.php');
    exit;
}

// Filter übernehmen
$filters = [
    'username'      => trim($_GET['username'] ?? ''),
    'min_attempts'  => trim($_GET['min_attempts'] ?? ''),
    'max_attempts'  => trim($_GET['max_attempts'] ?? ''),
];

// CSV-Export
$doExport = isset($_GET['export']) && $_GET['export'] === 'csv';
$lockedUsers = $adminRepository->getFilteredLockedUsers($filters);

if ($doExport) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=locked_users_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Benutzername', 'E-Mail', 'Fehlversuche']);
    foreach ($lockedUsers as $user) {
        fputcsv($output, [
            $user['id'],
            $user['username'],
            $user['email'],
            $user['failed_attempts'],
        ]);
    }
    fclose($output);
    exit;
}

// Smarty befüllen
$smarty->assign([
    'locked_users' => $lockedUsers,
    'filters'      => $filters,
    'username'     => $_SESSION['username'] ?? '',
    'isAdmin'      => ($_SESSION['role'] ?? '') === 'admin',
    'isMod'        => ($_SESSION['role'] ?? '') === 'mod',
    'flash'        => $_SESSION['flash'] ?? null,
]);

unset($_SESSION['flash']);

$smarty->display('locked_users.tpl');
