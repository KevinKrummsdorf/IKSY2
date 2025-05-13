<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

<<<<<<< HEAD
// ✅ Weiterleitung auch bei gesetztem Flash, wenn 2FA vollständig bestätigt ist
if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['2fa_passed'] ?? false) === true
) {
=======
if (isset($_SESSION['user_id']) && !isset($_SESSION['flash'])) {
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
    header('Location: dashboard.php');
    exit;
}

<<<<<<< HEAD
// Flash anzeigen, wenn vorhanden
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

=======
// Flash an Smarty übergeben
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']); // wichtig: erst NACH assign
}

// Seite anzeigen
>>>>>>> 4e0e75f0651890aeaabe1b48031e861e3f06d2e6
$smarty->display('index.tpl');
