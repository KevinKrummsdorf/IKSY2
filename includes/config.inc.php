<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use Smarty\Smarty;

// 1. Autoloading
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Verzeichnisse sicherstellen
$dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../cache',
    __DIR__ . '/../templates_c',
    __DIR__ . '/../logs',
    __DIR__ . '/../stats',
];
foreach ($dirs as $d) {
    if (!is_dir($d)) {
        @mkdir($d, 0775, true);
    }
}

// 3. .env laden (PFAD KORRIGIERT auf /private/)
try {
    $envDirectory = realpath(__DIR__ . '/../private');
    if (!$envDirectory || !file_exists($envDirectory . '/.env')) {
        die("KRITISCHER FEHLER: .env Datei nicht in /private/ gefunden.");
    }
    $dotenv = Dotenv::createImmutable($envDirectory);
    $dotenv->load();
} catch (Throwable $e) {
    die("Fehler beim Laden der Umgebungsvariablen: " . $e->getMessage());
}

// 4. Basis-Konfiguration initialisieren
$config = [];
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

// 5. Session-Einstellungen
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => $baseUrl ?: '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 6. CSRF-Token generieren
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================================================
 * Encryption Keys (Nur noch für Daten-Verschlüsselung)
 * ========================================================= */
$GLOBALS['config'] = [];
$config = &$GLOBALS['config'];

try {
    // Wir laden nur noch den Key für verschlüsselte Daten (falls benötigt)
    // Wenn du auch diesen nicht mehr brauchst, kannst du den try-block komplett löschen
    $rawKey = base64_decode($_ENV['HALITE_KEYFILE_BASE64'] ?? '', true);
    if ($rawKey) {
        $config['halite_key'] = new \ParagonIE\Halite\Symmetric\EncryptionKey(
            new \ParagonIE\HiddenString($rawKey)
        );
    }
} catch (Throwable $e) {
    // Wir loggen den Fehler nur, lassen die App aber weiterlaufen
    error_log('Halite-Key Info: ' . $e->getMessage());
}

// 7. Weitere Konfigurationen (Synchronisation mit Global)
$config = &$GLOBALS['config'];

$config['app_name'] = $_ENV['APP_NAME'] ?? 'StudyHub';
$config['base_url'] = $baseUrl;
$config['site_url'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $config['base_url'];
$config['use_pretty_urls'] = file_exists(__DIR__ . '/../config/pretty_urls_enabled');

$config['db'] = [
    'host' => $_ENV['DB_HOST'] ?? '',
    'name' => $_ENV['DB_DATABASE'] ?? '',
    'user' => $_ENV['DB_USERNAME'] ?? '',
    'pass' => $_ENV['DB_PASSWORD'] ?? '',
];

$config['mail'] = [
    'host'           => $_ENV['SMTP_HOST'] ?? '',
    'user'           => $_ENV['SMTP_USER'] ?? '',
    'pass'           => $_ENV['SMTP_PASS'] ?? '',
    'port'           => (int)($_ENV['SMTP_PORT'] ?? 587),
    'from'           => $_ENV['SMTP_FROM'] ?? 'noreply@example.com',
    'from_name'      => $_ENV['SMTP_FROM_NAME'] ?? 'StudyHub',
    'encryption'     => PHPMailer::ENCRYPTION_STARTTLS,
    'verify_subject' => 'Bitte bestätige deine E-Mail-Adresse',
    'reset_subject'  => 'Passwort zurücksetzen',
    'contact_email'  => $_ENV['CONTACT_EMAIL'] ?? ''
];

$config['recaptcha'] = [
    'site_key'   => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
    'secret_key' => $_ENV['RECAPTCHA_SECRET'] ?? '',
    'min_score'  => (float)($_ENV['RECAPTCHA_MIN_SCORE'] ?? 0.5),
    'actions'    => ['contact', 'login', 'register'],
    'log_file'   => __DIR__ . '/../logs/recaptcha.log',
];

// 8. Includes (NACHDEM $config befüllt wurde)
require_once __DIR__ . '/../includes/ip_utils.inc.php';
require_once __DIR__ . '/../includes/recaptcha.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';
require_once __DIR__ . '/../includes/group_invites.inc.php';
require_once __DIR__ . '/../includes/crypto.inc.php';
require_once __DIR__ . '/../includes/password_requirements.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/UserRepository.php';

// 9. Smarty Initialisierung
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

$smarty->assign([
    'base_url'           => $config['base_url'],
    'app_name'           => $config['app_name'],
    'use_pretty_urls'    => $config['use_pretty_urls'],
    'recaptcha_site_key' => $config['recaptcha']['site_key'],
    'isLoggedIn'         => isset($_SESSION['user_id']),
    'username'           => $_SESSION['username'] ?? null,
    'user_role'          => $_SESSION['role'] ?? 'guest',
    'isAdmin'            => ($_SESSION['role'] ?? '') === 'admin',
    'csrf_token'         => $_SESSION['csrf_token'] ?? '',
]);

function handle_error(int $code, string $reason = '', string $action = ''): void
{
    global $smarty;

    http_response_code($code);

    // Smarty-Variablen für das Template
    $smarty->assign('code', $code);
    $smarty->assign('reason', $reason);
    $smarty->assign('action', $action);

    // Template-Datei ermitteln
    $templateFile = "errors/{$code}.tpl";
    $fullPath = __DIR__ . '/../templates/' . $templateFile;

    if (!file_exists($fullPath)) {
        $templateFile = "errors/500.tpl";
    }

    // Template direkt anzeigen
    $smarty->display($templateFile);
    exit;
}


function build_url(string $path, array $params = []): string
{
    global $config;

    $path = trim($path, '/');
    $base = $config['base_url'] . '/';
    if ($path === '') {
        return $base;
    }

    $usePretty = $config['use_pretty_urls'];

    if ($path === 'profile' && isset($params['user'])) {
        $user = rawurlencode($params['user']);
        unset($params['user']);

        if ($usePretty) {
            $path = "profile/{$user}";
        } else {
            $path = 'profile.php';
            $params = ['user' => $user] + $params;
        }
    } elseif ($path === 'groups' && isset($params['name'])) {
        $name = rawurlencode($params['name']);
        unset($params['name']);

        if ($usePretty) {
            $path = "groups/{$name}";
        } else {
            $path = 'gruppe.php';
            $params = ['name' => $name] + $params;
        }
    }
    elseif (!$usePretty && $path === 'profile/my') {
        $path = 'profile.php';
    }
    elseif (strpos($path, 'uploads/') === 0) {
        $file = substr($path, strlen('uploads/'));
        if ($usePretty) {
            $path = 'uploads/' . $file;
        } else {
            $path = 'fetch_upload.php';
            $params = ['file' => $file] + $params;
        }
    }

    if (!$usePretty && substr($path, -4) !== '.php') {
        $segments = explode('/', $path, 2);
        if (substr($segments[0], -4) !== '.php') {
            $segments[0] .= '.php';
        }
        $path = implode('/', $segments);
    }

    $url = $base . $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

// Helper to build links with or without Pretty URLs
$smarty->registerPlugin('function', 'url', function(array $params) use ($config): string {
    $path = trim($params['path'] ?? '', '/');
    $file = $params['file'] ?? null;
    unset($params['path'], $params['file']);

    $base = $config['base_url'] . '/';
    if ($path === '' && $file === null) {
        return $base;
    }

    $usePretty = $config['use_pretty_urls'];

    if ($file !== null) {
        $file = ltrim($file, '/');
        if ($usePretty) {
            $url = $base . 'uploads/' . $file;
        } else {
            $url = $base . 'fetch_upload.php';
            $params = ['file' => $file] + $params;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    // Profile links with username parameter
    if ($path === 'profile' && isset($params['user'])) {
        $user = rawurlencode($params['user']);
        unset($params['user']);

        if ($usePretty) {
            $path = "profile/{$user}";
        } else {
            $path = 'profile.php';
            $params = ['user' => $user] + $params;
        }
    }
    // Group detail links with name parameter
    elseif ($path === 'groups' && isset($params['name'])) {
        $name = rawurlencode($params['name']);
        unset($params['name']);

        if ($usePretty) {
            $path = "groups/{$name}";
        } else {
            $path = 'gruppe.php';
            $params = ['name' => $name] + $params;
        }
    }
    // Own profile shortcut
    elseif (!$usePretty && $path === 'profile/my') {
        $path = 'profile.php';
    }

    if (!$usePretty && substr($path, -4) !== '.php') {
        $segments = explode('/', $path, 2);
        if (substr($segments[0], -4) !== '.php') {
            $segments[0] .= '.php';
        }
        $path = implode('/', $segments);
    }

    $url = $base . $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
});
return $config;

