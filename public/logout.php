<?php
declare(strict_types=1);

session_start();

// Session sauber beenden
session_unset();
session_destroy();

// Neues leeres Session-Cookie setzen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax'
        ]
    );
}

// Zurück zur Startseite mit Logout-Erfolgsmeldung
header('Location: index.php?logout=1');
exit;
