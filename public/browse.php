<?php
declare(strict_types=1);

// Konfiguration & Session laden
require_once __DIR__ . '/../includes/config.inc.php';
session_start();

// Datenbankverbindung holen
$pdo = DbFunctions::db_connect();

// Suchbegriff aus GET-Parameter holen und bereinigen
$searchTerm = $_GET['search'] ?? '';
$searchTerm = trim($searchTerm);

// Materialien laden (entweder alle oder gefiltert)
if ($searchTerm === '') {
    // Ohne Suchbegriff: alle Materialien
    $materials = DbFunctions::getAllMaterials();
} else {
    // Mit Suchbegriff: nur passende Materialien (siehe neue Funktion unten)
    $materials = DbFunctions::getMaterialsByTitle($searchTerm);
}

// Genehmigte Uploads laden
$uploads = DbFunctions::getApprovedUploads();

// Alle Uploader-IDs sammeln
$uploaderIds = array_unique(array_column($uploads, 'uploaded_by'));

// Profile laden, falls vorhanden
$profilesAssoc = [];
if (!empty($uploaderIds)) {
    $profiles = DbFunctions::getProfilesByUserIds($uploaderIds);
    $profilesAssoc = array_column($profiles, null, 'user_id');
}

// Prüfen, ob der Benutzer eingeloggt ist
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;

// Durchschnittliche Bewertungen für jedes Material berechnen
$averageRatings = [];
foreach ($materials as $material) {
    $average = DbFunctions::getAverageMaterialRating((int)$material['id']);
    $averageRatings[$material['id']] = $average ?: ['average_rating' => 0, 'total_ratings' => 0];
}

// Eigene Bewertungen des eingeloggten Nutzers holen
$userRatings = [];
if ($isLoggedIn) {
    foreach ($materials as $material) {
        $rating = DbFunctions::getUserMaterialRating((int)$material['id'], (int)$_SESSION['user_id']);
        if ($rating) {
            $userRatings[$material['id']] = $rating['rating'];
        }
    }
}

// Alle Daten an Smarty übergeben
$smarty->assign('searchTerm', $searchTerm);
$smarty->assign('materials', $materials);
$smarty->assign('uploads', $uploads);
$smarty->assign('profiles', $profilesAssoc);
$smarty->assign('isLoggedIn', $isLoggedIn);
$smarty->assign('averageRatings', $averageRatings);
$smarty->assign('userRatings', $userRatings);

// Template anzeigen
$smarty->display('browse.tpl');
