<?php
declare(strict_types=1);

// 0) Autoloader & Environment
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 1) Config laden (jetzt mit Umgebungsvariablen)
$config = require __DIR__ . '/../includes/config.inc.php';

// 2) Smarty & Session
session_start();
use Smarty\Smarty;

// Wenn bereits eingeloggt UND NICHT gerade frisch eingeloggt (login=success), direkt ins Dashboard
if (isset($_SESSION['user_id']) && !isset($_GET['login'])) {
    header('Location: dashboard.php');
    exit;
}

// Smarty initialisieren
$smarty = new Smarty();
$smarty->setTemplateDir([
    __DIR__ . '/../templates/',
    __DIR__ . '/../templates/layouts/',
    __DIR__ . '/../templates/partials/',
]);
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Globale Template-Variablen zuweisen
$smarty->assign('recaptcha_site_key', $config['recaptcha_site_key']);
$smarty->assign('base_url',           $config['base_url']);
$smarty->assign('app_name',           $config['app_name']);
$smarty->assign('isLoggedIn',         isset($_SESSION['user_id']));
$smarty->assign('username',           $_SESSION['username'] ?? null);

// Template anzeigen
$smarty->display('index.tpl');
