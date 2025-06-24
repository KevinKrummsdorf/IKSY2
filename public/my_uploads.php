<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriffsschutz
if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um deine Uploads zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId = (int)$_SESSION['user_id'];

$filters = [
    'title'       => trim($_GET['title'] ?? ''),
    'filename'    => trim($_GET['filename'] ?? ''),
    'course_name' => trim($_GET['course_name'] ?? ''),
    'from_date'   => trim($_GET['from_date'] ?? ''),
    'to_date'     => trim($_GET['to_date'] ?? ''),
];

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 25;
$offset      = ($currentPage - 1) * $pageSize;

$totalCount = DbFunctions::countFilteredUploadsByUser($userId, $filters);
$totalPages = (int)ceil($totalCount / $pageSize);
$uploads    = DbFunctions::getFilteredUploadsByUser($userId, $filters, $pageSize, $offset);
$suggestions = DbFunctions::getUserUploadSuggestions($userId);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_token'];

$smarty->assign([
    'uploads'     => $uploads,
    'currentPage' => $currentPage,
    'totalPages'  => $totalPages,
    'filters'     => $filters,
    'suggestions' => $suggestions,
    'username'    => $_SESSION['username'] ?? '',
    'csrf_token'  => $csrf,
]);

$smarty->display('my_uploads.tpl');
