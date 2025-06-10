<?php

$config = require __DIR__ . '/../includes/config.inc.php';
session_start();

$pdo = DbFunctions::db_connect();

// Materialien abrufen
$materials = DbFunctions::getAllMaterials();

// Uploads abrufen (nicht abgelehnt)
$uploads = DbFunctions::getApprovedUploads();

// Daten an Smarty übergeben
$smarty->assign('materials', $materials);
$smarty->assign('uploads', $uploads);

$smarty->display('browse.tpl');