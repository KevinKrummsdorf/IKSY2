<?php 
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.inc.php';

$config = require __DIR__ . '/../includes/config.inc.php'; // <- ACHTUNG auf .inc.php

use Smarty\Smarty;
/** @var Smarty $smarty */
$smarty = new Smarty();

$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

// Config-Werte an Smarty übergeben
$smarty->assign('base_url', $config['base_url']);
$smarty->assign('app_name', $config['app_name']);

// Session-Status an Smarty übergeben
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username', $_SESSION['username'] ?? '');
$smarty->display('profile.tpl')
?>