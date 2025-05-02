<?php
declare(strict_types=1);
session_start();

// 1) Nicht eingeloggt → zurück zur Startseite
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// 2) Dotenv laden (damit DB-Credentials aus .env verfügbar sind)
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 3) Rolle prüfen
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');

// 4) DB-Verbindung
require_once __DIR__ . '/../includes/db.inc.php';
$pdo = DbFunctions::db_connect();

// 5) Logs laden
require_once __DIR__ . '/../includes/fetch_logs.inc.php';
$logs = fetchLoginLogs($pdo, $isAdmin, 50);

// 6) Smarty initialisieren
require_once __DIR__ . '/../vendor/autoload.php'; // nur nötig, wenn noch nicht geladen
use Smarty\Smarty;
/** @var Smarty $smarty */
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

// 7) Globale Variablen
$config = require __DIR__ . '/../includes/config.inc.php';
$smarty->assign('base_url',   $config['base_url']);
$smarty->assign('app_name',   $config['app_name']);
$smarty->assign('isLoggedIn', true);
$smarty->assign('username',   $_SESSION['username'] ?? '');
$smarty->assign('isAdmin',    $isAdmin);
$smarty->assign('admin_logs', $logs);

// 8) Template rendern
$smarty->display('dashboard.tpl');
