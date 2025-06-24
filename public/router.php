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

$page = trim($_GET['page'] ?? '', '/');
$page = $page === '' ? 'index' : $page;

$type = null;
$file = null;

// Dynamische Routen
if ($page === 'dashboard') {
    $type = 'php';
    $file = 'dashboard.php';
} elseif ($page === 'profile/my') {
    $type = 'php';
    $file = 'profile.php';
} elseif (preg_match('#^profile/([^/]+)$#', $page, $m)) {
    $user = DbFunctions::fetchUserByIdentifier($m[1]);
    if ($user) {
        $_GET['id'] = $user['id'];
        $type = 'php';
        $file = 'profile.php';
    }
} elseif ($page === 'groups') {
    $type = 'php';
    $file = 'groups.php';
} elseif (preg_match('#^groups/([^/]+)$#', $page, $m)) {
    $name = urldecode($m[1]);
    $group = DbFunctions::fetchGroupByName($name);
    if ($group) {
        $_GET['id'] = $group['id'];
        $type = 'php';
        $file = 'gruppe.php';
    }
}

// Statische Seiten aus der Whitelist

if ($type === null && isset($validPages[$page])) {
    $type = $validPages[$page]['type'];
    $file = $validPages[$page]['file'];
}

// Fallback: existierende PHP-Datei ohne Endung
if ($type === null && file_exists(__DIR__ . "/{$page}.php")) {
    $type = 'php';
    $file = "{$page}.php";
}

if ($type === null || $file === null) {
    $reason = urlencode("Die Seite '$page' existiert nicht.");
    header("Location: /studyhub/error/404?reason={$reason}");
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
if ($type === 'tpl') {
    $smarty->display($file);
} elseif ($type === 'php') {
    $phpPath = __DIR__ . '/' . $file;
    if (file_exists($phpPath)) {
        require $phpPath;
    } else {
        $reason = urlencode("Die Datei '$file' existiert nicht.");
        header("Location: /studyhub/error/500?reason={$reason}");
        exit;
    }
} else {
    $reason = urlencode("Ungültiger Seitentyp: '$type'");
    header("Location: /studyhub/error/500?reason={$reason}");
    exit;
}
