<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use Smarty\Smarty;

// Konfiguration laden
$config = require_once __DIR__ . '/../includes/config.inc.php';

// Smarty Setup
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Globale Variablen
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username', $_SESSION['username'] ?? null);

// Template rendern
$smarty->display('privacy.tpl');
