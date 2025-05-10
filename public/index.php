<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

if (isset($_SESSION['user_id']) && !isset($_SESSION['flash'])) {
    header('Location: dashboard.php');
    exit;
}

// Flash an Smarty übergeben
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']); // wichtig: erst NACH assign
}

// Seite anzeigen
$smarty->display('index.tpl');
