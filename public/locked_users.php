<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once __DIR__ . '/../includes/config.inc.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Nur Admins erlaubt
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
if (! $isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    exit('Zugriff verweigert.');
}

// POST-Handler zum Entsperren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = (int) $_POST['user_id'];
    DbFunctions::unlockAccount($userId);

    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => 'Benutzerkonto wurde erfolgreich entsperrt.'
    ];

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    header("Location: locked_users.php?page={$page}");
    exit;
}

// E-Mail an User nach erfolgreicher entsperrung
$userData = DbFunctions::getUserById($userId);

if ($userData && !empty($userData['email']) && !empty($userData['username'])) {

    $subject = 'Dein StudyHub-Konto wurde entsperrt';
    $body = "
        <p>Hallo {$userData['username']},</p>
        <p>dein Benutzerkonto auf <strong>StudyHub</strong> wurde soeben durch einen Administrator entsperrt.</p>
        <p>Du kannst dich jetzt wieder wie gewohnt einloggen.</p>
        <p>Viele Grüße,<br>Dein StudyHub-Team</p>
    ";

    try {
        sendMail($userData['email'], $userData['username'], $subject, $body);
    } catch (Exception $e) {
        error_log("E-Mail-Versand fehlgeschlagen: " . $e->getMessage());
    }
}

// Pagination
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage     = 25;
$offset      = ($currentPage - 1) * $perPage;

// Daten abrufen
$totalCount  = DbFunctions::countLockedUsers();
$totalPages  = (int)ceil($totalCount / $perPage);
$lockedUsers = DbFunctions::getLockedUsersPage($perPage, $offset);

// Flash-Meldung
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Smarty-Zuweisungen
$smarty->assign('locked_users',  $lockedUsers);
$smarty->assign('currentPage',   $currentPage);
$smarty->assign('totalPages',    $totalPages);
$smarty->assign('isAdmin',       $isAdmin);
$smarty->assign('username',      $_SESSION['username'] ?? '');

// Anzeigen
$smarty->display('locked_users.tpl');
