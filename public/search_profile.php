<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UserRepository.php';

if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}

$searchTerm = $_GET['q'] ?? '';
$results = [];

if ($searchTerm !== '') {
    try {
        $db = new Database();
        $userRepository = new UserRepository($db);
        $results = $userRepository->searchUsers($searchTerm);
    } catch (Throwable $e) {
        echo 'Fehler bei der Datenbanksuche: ' . $e->getMessage();
        exit;
    }
}


// Smarty-Zuweisungen

$smarty->assign('isLoggedIn', true);
$smarty->assign('searchTerm', htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'));
$smarty->assign('results', $results);

$smarty->display('search_profile.tpl');
