<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';  // Lädt automatisch Smarty, $config, Session, etc.

// Team-Daten für Smarty (Dummy-Daten für Demo-Version)
$smarty->assign('team', [
    [
        'name' => 'Max Mustermann',
        'img'  => 'default_person.png',
        'bio'  => 'Max ist ein fiktives Teammitglied für diese Demo. Er unterstützt das Projekt mit seiner langjährigen Erfahrung im Bereich der Softwareentwicklung.'
    ],
    [
        'name' => 'Erika Musterfrau',
        'img'  => 'default_person.png',
        'bio'  => 'Erika ist ein fiktives Teammitglied für diese Demo. Sie kümmert sich um das Design und die Benutzererfahrung der Plattform.'
    ],
    [
        'name' => 'John Doe',
        'img'  => 'default_person.png',
        'bio'  => 'John ist ein fiktives Teammitglied für diese Demo. Er ist für die Server-Infrastruktur und Datenbank-Optimierung zuständig.'
    ],
    [
        'name' => 'Jane Doe',
        'img'  => 'default_person.png',
        'bio'  => 'Jane ist ein fiktives Teammitglied für diese Demo. Sie leitet das Marketing und die Kommunikation nach außen.'
    ]
]);

$smarty->display('about.tpl');
