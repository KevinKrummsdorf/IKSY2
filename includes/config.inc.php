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
    'contact_email' => 'studyhub.iksy@gmail.com',
    'recaptcha_site_key' => (string)($_ENV['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY')   ?? ''),
    'recaptcha_secret'   => (string)($_ENV['RECAPTCHA_SECRET']     ?? getenv('RECAPTCHA_SECRET')       ?? ''),
];
