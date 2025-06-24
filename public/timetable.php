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
$success = false;

// === Formular absenden ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        DbFunctions::saveUserSchedule($userId, $_POST['timetable'] ?? []);
        header('Location: timetable.php?success=1');
        exit;
    } catch (Throwable $e) {
        echo "<pre>Fehler beim Speichern:\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
        exit;
    }
}

// === Daten laden ===
$weekdays  = DbFunctions::fetchAllWeekdays();    // id, day_name
$timeSlots = DbFunctions::fetchAllTimeSlots();   // id, start_time, end_time
$timetable = DbFunctions::fetchUserSchedule($userId); // [weekday_id][slot_id] => array

// === Smarty anzeigen ===
$smarty->assign('title', 'Stundenplan');
$smarty->assign('weekdays', $weekdays);
$smarty->assign('timeSlots', $timeSlots);
$smarty->assign('timetable', $timetable);
$smarty->assign('success', isset($_GET['success']));
$smarty->display('timetable.tpl');
