<?php
// Aktiviert die Anzeige von Fehlern zur Laufzeit – wichtig für die Entwicklung
ini_set('display_errors', 1);             // Zeigt allgemeine Laufzeitfehler an
ini_set('display_startup_errors', 1);     // Zeigt Fehler beim Starten von PHP an (z. B. bei Extension-Problemen)
error_reporting(E_ALL);                   // Meldet alle Fehlertypen (Empfehlung für Entwicklung)

// Lädt die zentrale Konfigurationsdatei (z. B. Datenbankverbindung, Smarty-Setup usw.)
require_once __DIR__ . '/../includes/config.inc.php';

// Setzt eine Variable namens "title", die im Template verwendet werden kann (z. B. im <title>-Tag oder Überschrift)
$smarty->assign('title', 'Stundenplan-Test');

// Zeigt das Template "stundenplan.tpl" an
// Smarty ersetzt dort alle Variablen und Blöcke durch Inhalte und gibt das fertige HTML aus
$smarty->display('stundenplan.tpl');
