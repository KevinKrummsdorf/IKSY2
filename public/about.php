<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';  // Lädt automatisch Smarty, $config, Session, etc.

// Team-Daten für Smarty
$smarty->assign('team', [
    [
        'name' => 'Elina Dobzenko',
        'img'  => 'elina.png',
        'bio'  => 'Elina Dobzenko treibt bei Studyhub mit viel Energie und klarem Fokus Projekte voran. Sie denkt lösungsorientiert, handelt pragmatisch und hat dabei immer im Blick, wie das Team gemeinsam schneller und besser ans Ziel kommt. Stillstand? Gibt’s bei ihr nicht.'
    ],
    [
        'name' => 'Fiete Timmer',
        'img'  => 'fiete.png',
        'bio'  => 'Fiete Timmer fuchst sich in jedes Thema rein – ganz gleich, wie komplex. Mit Neugier, Grips und Ausdauer wird er schnell zum Experten und ist aus keinem Projekt mehr wegzudenken.'
    ],
    [
        'name' => 'Kevin Krummsdorf',
        'img'  => 'kevin.svg',
        'bio'  => 'Kevin Krummsdorf ist der Kopf hinter vielen Abläufen bei Studyhub – ohne ihn läuft nichts. Mit strategischem Denken, technischer Übersicht und einem feinen Gespür für Teamdynamik sorgt er dafür, dass Ideen nicht nur gut klingen, sondern auch funktionieren.'
    ],
    [
        'name' => 'Marla Brückner',
        'img'  => 'marla.png',
        'bio'  => 'Marla Brückner ist bei Studyhub eine echte Macherin – sie packt bei allem mit an, denkt mit, organisiert und unterstützt genau da, wo’s gebraucht wird. Verlässlich, engagiert und immer mit vollem Einsatz.'
    ],
    [
        'name' => 'Salome Balke',
        'img'  => 'salome.png',
        'bio'  => 'Salome Balke ist bei Studyhub eine verlässliche Ansprechpartnerin, die mit Engagement, Lösungsorientierung und Feingefühl für reibungslose Abläufe sorgt.'
    ],
]);

$smarty->display('about.tpl');
