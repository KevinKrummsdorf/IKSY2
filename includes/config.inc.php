<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;

// Autoloading und DB-Initialisierung
require_once __DIR__ . '/../vendor/autoload.php';

// Ensure writable directories exist
$dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../cache',
    __DIR__ . '/../templates_c',
    __DIR__ . '/../logs',
    __DIR__ . '/../stats',
];
foreach ($dirs as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0775, true);
    }
}

require_once __DIR__ . '/../includes/db.inc.php';
require_once __DIR__ . '/../includes/ip_utils.inc.php';
require_once __DIR__ . '/../includes/recaptcha.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';
require_once __DIR__ . '/../includes/group_invites.inc.php';
require_once __DIR__ . '/../includes/central_logs.inc.php';
require_once __DIR__ . '/../includes/crypto.inc.php';

// .env laden
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Session starten
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Konfigurationen

// Halite Key
try {
    $rawKey = base64_decode($_ENV['HALITE_KEYFILE_BASE64'] ?? '', true);
    if (!$rawKey) {
        throw new \RuntimeException('Ungültiger Base64-Schlüssel.');
    }

    $config['halite_key'] = KeyFactory::importEncryptionKey(
        new HiddenString($rawKey)
    );
} catch (Throwable $e) {
    error_log('Fehler beim Laden des Halite-Keys: ' . $e->getMessage());
    http_response_code(500);
    exit('Fehlerhafte Schlüsselkonfiguration');
}

// Aufbewahrungsdauer für abgelehnte Uploads in Tagen
$config['uploads'] = [
    'rejected_retention_days' => 30
];

$config['app_name']  = $_ENV['APP_NAME'] ?? 'StudyHub';
$config['base_url'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$config['site_url'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $config['base_url'];
$config['use_pretty_urls'] = file_exists(__DIR__ . '/../config/pretty_urls_enabled');


//DB
$config['db'] = [
    'host' => $_ENV['DB_HOST'],
    'name' => $_ENV['DB_DATABASE'],
    'user' => $_ENV['DB_USERNAME'],
    'pass' => $_ENV['DB_PASSWORD'],
];

//Mailer
$config['mail'] = [
    'host'       => $_ENV['SMTP_HOST'] ?? '',
    'user'       => $_ENV['SMTP_USER'] ?? '',
    'pass'       => $_ENV['SMTP_PASS'] ?? '',
    'port'       => (int)($_ENV['SMTP_PORT'] ?? 587),
    'from'       => $_ENV['SMTP_FROM'] ?? 'noreply@example.com',
    'from_name'  => $_ENV['SMTP_FROM_NAME'] ?? 'StudyHub',
    'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
    'verify_subject' => 'Bitte bestätige deine E-Mail-Adresse',
    'reset_subject'  => 'Passwort zurücksetzen',
    'contact_email' => $_ENV['CONTACT_EMAIL'] ?? ''
];

//reCaptcha
$config['recaptcha'] = [
    'site_key'   => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
    'secret_key' => $_ENV['RECAPTCHA_SECRET']   ?? '',
    'min_score'  => (float)($_ENV['RECAPTCHA_MIN_SCORE'] ?? 0.5),
    'actions'    => ['contact', 'login', 'register'],
    'log_file'   => __DIR__ . '/../logs/recaptcha.log',
];


// ==== Smarty Initialisierung ====
use Smarty\Smarty;
$smarty = new Smarty();


$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

// Globale Smarty-Variablen
$smarty->assign('base_url',   $config['base_url']);
$smarty->assign('app_name',   $config['app_name']);
$smarty->assign('use_pretty_urls', $config['use_pretty_urls']);
$smarty->assign('recaptcha_site_key', $config['recaptcha']['site_key']);
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username',   $_SESSION['username'] ?? null);
$smarty->assign('user_role', $_SESSION['role'] ?? 'guest');
$smarty->assign('isAdmin', ($_SESSION['role'] ?? '') === 'admin');

/**
 * Handle HTTP errors. If Pretty URLs and custom error pages are enabled the
 * user is redirected to the error script. Otherwise a plain HTTP status code is
 * emitted so the web server shows its default page.
 */
function handle_error(int $code, string $reason = '', string $action = ''): void
{
    global $config;

    if ($config['use_pretty_urls']) {
        $params = [];
        if ($reason !== '') {
            $params['reason'] = $reason;
        }
        if ($action !== '') {
            $params['action'] = $action;
        }
        $url = build_url("error/{$code}");
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: $url");
    } else {
        http_response_code($code);
        if ($reason !== '') {
            echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
        }
    }
    exit;
}

/**
 * Build an application URL that respects Pretty URL settings.
 */
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

