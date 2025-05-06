<?php
// PHP-Fehler anzeigen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Smarty-Fehler-Reporting
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use Smarty\Smarty;

// Smarty konfigurieren
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Fehler-Reporting für Smarty einschalten
//$smarty->muteExpectedErrors(false);
//$smarty->error_reporting = E_ALL;

// Globale Variablen
$config = require_once __DIR__ . '/../includes/config.inc.php';
$smarty->assign('base_url',    $config['base_url']);
$smarty->assign('app_name',    $config['app_name']);
$smarty->assign('isLoggedIn',  isset($_SESSION['user_id']));
$smarty->assign('username',    $_SESSION['username'] ?? null);

// Team-Daten für about.tpl
$team = [
    [
        'name' => 'Elina Dobzenko',
        'img'  => 'elina.svg',
        'bio'  => 'Elina Dobzenko treibt bei Studyhub mit viel Energie und klarem Fokus Projekte voran. Sie denkt lösungsorientiert, handelt pragmatisch und hat dabei immer im Blick, wie das Team gemeinsam schneller und besser ans Ziel kommt. Stillstand? Gibt’s bei ihr nicht.'
    ],
    [
        'name' => 'Fiete Timmer',
        'img'  => 'fiete.svg',
        'bio'  => 'Fiete Timmer fuchst sich in jedes Thema rein – ganz gleich, wie komplex. Mit Neugier, Grips und Ausdauer wird er schnell zum Experten und ist aus keinem Projekt mehr wegzudenken.'
    ],
    [
        'name' => 'Kevin Krummsdorf',
        'img'  => 'kevin.svg',
        'bio'  => 'Kevin Krummsdorf ist der Kopf hinter vielen Abläufen bei Studyhub – ohne ihn läuft nichts. Mit strategischem Denken, technischer Übersicht und einem feinen Gespür für Teamdynamik sorgt er dafür, dass Ideen nicht nur gut klingen, sondern auch funktionieren.'
    ],
    [
        'name' => 'Marla Brückner',
        'img'  => 'marla.svg',
        'bio'  => 'Marla Brückner ist bei Studyhub eine echte Macherin – sie packt bei allem mit an, denkt mit, organisiert und unterstützt genau da, wo’s gebraucht wird. Verlässlich, engagiert und immer mit vollem Einsatz.'
    ],
    [
        'name' => 'Salome Balke',
        'img'  => 'salome.svg',
        'bio'  => 'Salome Balke ist bei Studyhub eine verlässliche Ansprechpartnerin, die mit Engagement, Lösungsorientierung und Feingefühl für reibungslose Abläufe sorgt.'
    ],
];

$smarty->assign('team', $team);

// Template rendern
$smarty->display('about.tpl');
