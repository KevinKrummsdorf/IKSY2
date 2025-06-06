<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use Smarty\Smarty;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;

// Autoloading und DB-Initialisierung
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.inc.php';
require_once __DIR__ . '/../includes/ip_utils.inc.php';
require_once __DIR__ . '/../includes/recaptcha.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';
require_once __DIR__ . '/../includes/central_logs.inc.php';
require_once __DIR__ . '/../includes/crypto.inc.php';
require_once __DIR__ . '/../includes/logger.inc.php';
require_once __DIR__ . '/../src/ILogger.php';
require_once __DIR__ . '/../src/MonologLoggerAdapter.php';
require_once __DIR__ . '/../src/LoggerFactory.php';


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

//Logger
$config['log'] = [
    'debug'    => true,
    'log_days' => 30
];

$config['app_name']  = $_ENV['APP_NAME'] ?? 'StudyHub';
$config['base_url'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$config['site_url'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $config['base_url'];


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
$smarty = new Smarty();
$smarty->escape_html = true;

$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

// Globale Smarty-Variablen
$smarty->assign('base_url',   $config['base_url']);
$smarty->assign('app_name',   $config['app_name']);
$smarty->assign('recaptcha_site_key', $config['recaptcha']['site_key']);
$smarty->assign('isLoggedIn', isset($_SESSION['user_id']));
$smarty->assign('username',   $_SESSION['username'] ?? null);
$smarty->assign('user_role', $_SESSION['role'] ?? 'guest');
$smarty->assign('isAdmin', ($_SESSION['role'] ?? '') === 'admin');
return $config;

