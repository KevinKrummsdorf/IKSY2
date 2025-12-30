<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Konfiguration & Session laden
require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UploadRepository.php';
require_once __DIR__ . '/../src/Repository/ProfileRepository.php';

// Datenbankverbindung holen
$db = new Database();
$uploadRepository = new UploadRepository($db);
$profileRepository = new ProfileRepository($db);


// Suchbegriff aus GET-Parameter holen und bereinigen
$searchTerm = $_GET['search'] ?? '';
$searchTerm = trim($searchTerm);

// Materialien laden (entweder alle oder gefiltert)
if ($searchTerm === '') {
    // Ohne Suchbegriff: alle Materialien
    $materials = $uploadRepository->getAllMaterials();
} else {
    // Mit Suchbegriff: nur passende Materialien (siehe neue Funktion unten)
    $materials = $uploadRepository->getMaterialsByTitle($searchTerm);
}

// Genehmigte Uploads laden
$uploads = $uploadRepository->getApprovedUploads();

// Uploads nach Material gruppieren, um spätere Suchschleifen zu vermeiden
$uploadsByMaterial = [];
foreach ($uploads as $up) {
    $uploadsByMaterial[$up['material_id']][] = $up;
}

// Alle Uploader-IDs sammeln
$uploaderIds = array_unique(array_column($uploads, 'uploaded_by'));

// Profile laden, falls vorhanden
$profilesAssoc = [];
if (!empty($uploaderIds)) {
    $profiles = $profileRepository->getProfilesByUserIds($uploaderIds);
    $profilesAssoc = array_column($profiles, null, 'user_id');
}

// Prüfen, ob der Benutzer eingeloggt ist
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;

// IDs aller Materialien sammeln
$materialIds = array_column($materials, 'id');

// Durchschnittliche Bewertungen gebündelt laden
$averageRatings = $uploadRepository->getAverageRatingsForMaterials($materialIds);
foreach ($materialIds as $id) {
    $averageRatings[$id] ??= ['average_rating' => 0, 'total_ratings' => 0];
}

// Eigene Bewertungen gebündelt laden
$userRatings = [];
if ($isLoggedIn) {
    $userRatings = $uploadRepository->getUserRatingsForMaterials($materialIds, (int)$_SESSION['user_id']);
}

// Alle Daten an Smarty übergeben
$smarty->assign('searchTerm', $searchTerm);
$smarty->assign('materials', $materials);
$smarty->assign('uploads', $uploads); // bisherige Nutzung
$smarty->assign('uploadsByMaterial', $uploadsByMaterial);
$smarty->assign('profiles', $profilesAssoc);
$smarty->assign('isLoggedIn', $isLoggedIn);
$smarty->assign('averageRatings', $averageRatings);
$smarty->assign('userRatings', $userRatings);

// Template anzeigen
$smarty->display('browse.tpl');
