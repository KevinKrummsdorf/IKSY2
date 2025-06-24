<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.inc.php';

// Whitelist der gültigen Seiten
$validPages = [
    'impressum'   => ['type' => 'tpl', 'file' => 'impressum.tpl'],
    'kontakt'     => ['type' => 'tpl', 'file' => 'contact.tpl'],
    'agb'         => ['type' => 'tpl', 'file' => 'terms.tpl'],
    'datenschutz' => ['type' => 'tpl', 'file' => 'privacy.tpl'],
    'about'       => ['type' => 'php', 'file' => 'about.php'],
];

$page = $_GET['page'] ?? 'start';

if (!isset($validPages[$page])) {
    $reason = urlencode("Die Seite '$page' existiert nicht.");
    header("Location: {$config['base_url']}/error.php?code=404&reason={$reason}");
    exit;
}

// Allgemeine Smarty-Variablen setzen
$smarty->assign([
    'base_url'   => $config['base_url'],
    'app_name'   => $config['app_name'],
    'isLoggedIn' => isset($_SESSION['user_id']),
    'username'   => $_SESSION['username'] ?? null,
]);

// Typ prüfen: Smarty oder PHP
$type = $validPages[$page]['type'];
$file = $validPages[$page]['file'];

if ($type === 'tpl') {
    $smarty->display($file);
} elseif ($type === 'php') {
    $phpPath = __DIR__ . '/' . $file;
    if (file_exists($phpPath)) {
        require $phpPath;
    } else {
        $reason = urlencode("Die Datei '$file' existiert nicht.");
        header("Location: {$config['base_url']}/error.php?code=500&reason={$reason}");
        exit;
    }
} else {
    $reason = urlencode("Ungültiger Seitentyp: '$type'");
    header("Location: {$config['base_url']}/error.php?code=500&reason={$reason}");
    exit;
}
