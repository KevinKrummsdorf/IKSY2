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

// === Export-Funktionen ===
$export = $_GET['export'] ?? '';
if ($export === 'csv' || $export === 'pdf') {
    $weekdays  = DbFunctions::fetchAllWeekdays();
    $timeSlots = DbFunctions::fetchAllTimeSlots();
    $timetable = DbFunctions::fetchUserSchedule($userId);

    if ($export === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=timetable.csv');

        $out = fopen('php://output', 'w');
        $header = ['Zeit'];
        foreach ($weekdays as $day) {
            $header[] = $day['day_name'];
        }
        fputcsv($out, $header);

        foreach ($timeSlots as $slot) {
            $row = [
                date('H:i', strtotime($slot['start_time'])) . ' - ' .
                date('H:i', strtotime($slot['end_time']))
            ];
            foreach ($weekdays as $day) {
                $entry = $timetable[$day['id']][$slot['id']] ?? null;
                $cell = '';
                if ($entry) {
                    $cell = $entry['subject'];
                    if ($entry['room'] !== '') {
                        $cell .= ' (' . $entry['room'] . ')';
                    }
                }
                $row[] = $cell;
            }
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    if ($export === 'pdf') {
        $pdf = new TCPDF();
        $pdf->SetCreator('StudyHub');
        $pdf->SetAuthor($_SESSION['username'] ?? '');
        $pdf->SetTitle('Stundenplan');
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $html = '<h2>Stundenplan</h2><table border="1" cellpadding="4">';
        $html .= '<tr><th>Zeit</th>';
        foreach ($weekdays as $day) {
            $html .= '<th>' . htmlspecialchars($day['day_name']) . '</th>';
        }
        $html .= '</tr>';

        foreach ($timeSlots as $slot) {
            $html .= '<tr>';
            $slotRange = date('H:i', strtotime($slot['start_time'])) .
                ' - ' . date('H:i', strtotime($slot['end_time']));
            $html .= '<td>' . $slotRange . '</td>';
            foreach ($weekdays as $day) {
                $entry = $timetable[$day['id']][$slot['id']] ?? null;
                $cell = '';
                if ($entry) {
                    $cell = htmlspecialchars($entry['subject']);
                    if ($entry['room'] !== '') {
                        $cell .= '<br>(' . htmlspecialchars($entry['room']) . ')';
                    }
                }
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        $pdf->writeHTML($html);
        $pdf->Output('timetable.pdf', 'D');
        exit;
    }
}

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
