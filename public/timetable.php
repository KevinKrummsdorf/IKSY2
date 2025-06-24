<?php
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.inc.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prüfen, ob Nutzer eingeloggt ist
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    $reason = urlencode("Du musst eingeloggt sein, um dein Stundenplan zu sehen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$days = ['montag', 'dienstag', 'mittwoch', 'donnerstag', 'freitag'];

// Zeitbereiche vorberechnen (08:00 - 09:00 bis 19:00 - 20:00)
$timeSlots = [];
$slotTimes = []; // interne Startzeit-Werte für Speicherung
for ($h = 8; $h <= 19; $h++) {
    $start = sprintf('%02d:00', $h);
    $end = sprintf('%02d:00', $h + 1);
    $timeSlots[] = "$start - $end";
    $slotTimes[] = $start; // wird in DB gespeichert
}

// === Formular absenden ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    DbFunctions::deleteAllTimetableEntries($userId);
    
    foreach ($days as $day) {
        foreach ($slotTimes as $index => $time) {
            $subject = trim($_POST['timetable'][$day][$index]['fach'] ?? '');
            $room    = trim($_POST['timetable'][$day][$index]['raum'] ?? '');
            
            if ($subject !== '' || $room !== '') {
                DbFunctions::insertTimetableEntry($userId, $day, $time, $subject, $room, $index);
            }
        }
    }
    
    header('Location: timetable?success=1');
    exit;
}

// === Stundenplan laden ===
$timetable = [];
foreach ($days as $day) {
    $entries = DbFunctions::getTimetableByDay($userId, $day);
    $timetable[$day] = [];
    foreach ($entries as $entry) {
        $timetable[$day][$entry['slot_index']] = $entry;
    }
}

// === Smarty anzeigen ===
$smarty->assign('title', 'Stundenplan');
$smarty->assign('days', $days);
$smarty->assign('timeSlots', $timeSlots); // Anzeige: „08:00 - 09:00“ etc.
$smarty->assign('timetable', $timetable);
$smarty->assign('success', isset($_GET['success']));
$smarty->display('timetable.tpl');
