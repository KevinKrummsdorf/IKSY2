<?php
declare(strict_types=1);

session_start();

// Session leeren & zerstören
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

// Neue Session ID erzeugen und starten
session_start();
session_regenerate_id(true); // 💡 wichtig

// Flash setzen
$_SESSION['flash'] = [
    'type'    => 'success',
    'message' => 'Du wurdest erfolgreich ausgeloggt.',
    'context' => 'logout'
];

// Weiterleiten
header('Location: index');
exit;
