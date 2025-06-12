<?php
// Fehleranzeige aktivieren – hilfreich beim Entwickeln
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Konfiguration laden (Smarty, DB, Session, etc.)
require_once __DIR__ . '/../includes/config.inc.php';

// Session starten, falls noch nicht aktiv
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prüfen, ob Nutzer eingeloggt ist
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Stundenplan zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");    exit;
}
$userId = (int) $_SESSION['user_id'];

// Wochentage definieren
$days = ['montag', 'dienstag', 'mittwoch', 'donnerstag', 'freitag'];

// === Verarbeitung der Formular-Daten ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bisherige Einträge dieses Nutzers löschen
    DbFunctions::deleteAllTimetableEntries($userId);
    
    // Neue Einträge aus Formular speichern
    foreach ($_POST['timetable'] as $day => $entries) {
        foreach ($entries as $index => $data) {
            $time    = $_POST['time'][$index] ?? '';
            $subject = trim($data['fach'] ?? '');
            $room    = trim($data['raum'] ?? '');
            
            if ($subject !== '' || $room !== '') {
                DbFunctions::insertTimetableEntry($userId, $day, $time, $subject, $room, $index);
            }
        }
    }
    
    // Nach dem Speichern zurück zur Seite mit Erfolgsmeldung
    header('Location: timetable.php?success=1');
    exit;
}

// === Stundenplan für diesen Nutzer laden ===
$timetable = [];
foreach ($days as $day) {
    $timetable[$day] = DbFunctions::getTimetableByDay($userId, $day);
}

// An Smarty übergeben
$smarty->assign('title', 'Stundenplan');
$smarty->assign('timetable', $timetable);
$smarty->assign('success', isset($_GET['success']));
$smarty->display('timetable.tpl');
