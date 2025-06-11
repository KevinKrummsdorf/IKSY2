<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Zugriffsschutz: nur eingeloggte Nutzer
if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um den Stundenplan zu nutzen.");
    header("Location: /studyhub/error/403?reason={$reason}&action=both");
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$planDir  = __DIR__ . '/../uploads/stundenplaene';
$planFile = $planDir . '/plan_' . $userId . '.json';
$success  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zeiten = $_POST['zeit'] ?? [];
    $inputPlan = $_POST['stundenplan'] ?? [];
    $days  = ['montag','dienstag','mittwoch','donnerstag','freitag'];
    $saved = [];

    foreach ($days as $day) {
        for ($i = 0; $i < 10; $i++) {
            $zeit = $zeiten[$i] ?? '';
            $fach = $inputPlan[$day][$i]['fach'] ?? '';
            $raum = $inputPlan[$day][$i]['raum'] ?? '';
            if ($zeit !== '' || $fach !== '' || $raum !== '') {
                $saved[$day][$i] = [
                    'zeit' => $zeit,
                    'fach' => $fach,
                    'raum' => $raum,
                ];
            }
        }
    }

    if (!is_dir($planDir)) {
        mkdir($planDir, 0777, true);
    }
    file_put_contents($planFile, json_encode($saved));
    $success = true;
}

$plan = [];
if (file_exists($planFile)) {
    $json = file_get_contents($planFile);
    $plan = json_decode($json, true) ?? [];
}

$smarty->assign('stundenplan', $plan);
$smarty->assign('success', $success);

$smarty->display('stundenplan.tpl');

