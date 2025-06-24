<?php
declare(strict_types=1);

session_start();

// Admin-Login-Schutz
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header('Location: index');
    exit;
}

// Composer Autoload
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.inc.php';

// >>>> HIER: Smarty importieren
use Smarty\Smarty;

/** @var Smarty $smarty */
$smarty = new Smarty();

// Standard-SMARTY-Einstellungen
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

try {
    $pdo = DbFunctions::db_connect();

    $stmt = $pdo->query('SELECT * FROM login_logs ORDER BY created_at DESC LIMIT 50');
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    $logs = [];
}

// Smarty Variablen setzen
$smarty->assign('logs', $logs);

// Rendern
$smarty->display('admin_logs.tpl');
