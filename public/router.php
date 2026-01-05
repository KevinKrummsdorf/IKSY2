<?php
declare(strict_types=1);

// Session wird bereits in config.inc.php gestartet
require_once __DIR__ . '/../includes/config.inc.php';

// Whitelist der gültigen Seiten
$validPages = [
    'impressum'   => ['type' => 'tpl', 'file' => 'impressum.tpl'],
    'kontakt'     => ['type' => 'tpl', 'file' => 'contact.tpl'],
    'agb'         => ['type' => 'tpl', 'file' => 'terms.tpl'],
    'datenschutz' => ['type' => 'tpl', 'file' => 'privacy.tpl'],
    'about'       => ['type' => 'php', 'file' => 'about.php'],
];

// Page aus GET-Parameter
$page = $_GET['page'] ?? 'start';

// ==== Error-Seiten direkt erkennen ====
if (preg_match('#^error/([0-9]{3})$#', $page, $matches)) {
    $code = (int)$matches[1];
    handle_error($code);
}

// ==== Prüfen, ob Seite gültig ist ====
if (!isset($validPages[$page])) {
    handle_error(404, "Die Seite '$page' existiert nicht.");
}

// ==== Smarty globale Variablen setzen ====
$smarty->assign([
    'base_url'   => $config['base_url'],
    'app_name'   => $config['app_name'],
    'isLoggedIn' => isset($_SESSION['user_id']),
    'username'   => $_SESSION['username'] ?? null,
    'user_role'  => $_SESSION['role'] ?? 'guest',
    'isAdmin'    => ($_SESSION['role'] ?? '') === 'admin',
]);

// ==== Template oder PHP-Datei laden ====
$type = $validPages[$page]['type'];
$file = $validPages[$page]['file'];

if ($type === 'tpl') {
    $templateFile = __DIR__ . '/../templates/' . $file;
    if (!file_exists($templateFile)) {
        handle_error(500, "Template '$file' existiert nicht.");
    }
    $smarty->display($file);
} elseif ($type === 'php') {
    $phpPath = __DIR__ . '/' . $file;
    if (file_exists($phpPath)) {
        require $phpPath;
    } else {
        handle_error(500, "Datei '$file' existiert nicht.");
    }
} else {
    handle_error(500, "Ungültiger Seitentyp: '$type'");
}
