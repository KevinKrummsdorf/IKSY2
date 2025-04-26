<?php

$config = require __DIR__ . '/../includes/config.inc.php'; // <- ACHTUNG auf .inc.php

require_once __DIR__ . '/../vendor/autoload.php';
/** @var \Smarty\Smarty $smarty */


use Smarty\Smarty;

session_start();

// Konfiguration laden
require_once __DIR__ . '/../includes/config.inc.php'; // ACHTUNG auf .inc.php

// Smarty initialisieren
$smarty = new Smarty();
$smarty->setTemplateDir([
    __DIR__ . '/../templates/',
    __DIR__ . '/../templates/layouts/',
    __DIR__ . '/../templates/partials/',
]);
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Globale Template-Variablen zuweisen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username', $_SESSION['username'] ?? null);

// Template anzeigen
$smarty->display('index.tpl');
