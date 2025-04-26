<?php

// Basis-URL automatisch berechnen
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = "$protocol://$host$basePath";

// Konfigurationswerte zurückgeben
return [
    'base_url' => $base_url,
    'app_name' => 'StudyHub',
];
