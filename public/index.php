<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// ✅ Weiterleitung auch bei gesetztem Flash, wenn 2FA vollständig bestätigt ist
if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['2fa_passed'] ?? false) === true
) {
    header('Location: dashboard.php');
    exit;
}

// Flash anzeigen, wenn vorhanden
if (isset($_SESSION['flash'])) {
    $smarty->assign('flash', $_SESSION['flash']);
    unset($_SESSION['flash']);
}

$smarty->display('index.tpl');
