<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.inc.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt.');
}

$searchTerm = $_GET['q'] ?? '';
$results = [];

if ($searchTerm !== '') {
    try {
        $pdo = DbFunctions::db_connect();
        $stmt = $pdo->prepare("
            SELECT id, username AS result_username
            FROM users
            WHERE username LIKE :term1 OR email LIKE :term2
        ");
        $stmt->execute([
            ':term1' => '%' . $searchTerm . '%',
            ':term2' => '%' . $searchTerm . '%'
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
