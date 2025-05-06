<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
// .env laden
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
session_start();

use Smarty\Smarty;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PDO;

$config = require __DIR__ . '/../includes/config.inc.php';
require __DIR__ . '/../includes/recaptcha.inc.php';
require __DIR__ . '/../includes/db.inc.php';  // hier deine DbFunctions

// 1) PDO via DbFunctions holen
$pdo = DbFunctions::db_connect();

// Logger (optional)
$log = new Logger('contact');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/mail.log', Logger::DEBUG));

// Smarty initialisieren
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Globale Template-Variablen
$smarty->assign('base_url',           $config['base_url']);
$smarty->assign('app_name',           $config['app_name']);
$smarty->assign('contact_email',      $config['contact_email']);
$smarty->assign('recaptcha_site_key', $config['recaptcha_site_key']);

$errors    = [];
$success   = false;
$input     = ['name'=>'','email'=>'','subject'=>'','message'=>''];
$contactId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) reCAPTCHA prüfen & in DB loggen
    $token = $_POST['recaptcha_token'] ?? '';
    if (!recaptcha_verify($pdo, $token, $config['recaptcha_secret'])) {
        $errors[] = 'reCAPTCHA-Validierung fehlgeschlagen. Bitte erneut versuchen.';
    }

    // 3) Felder trimmen & validieren
    foreach (['name','email','subject','message'] as $f) {
        $input[$f] = trim((string)($_POST[$f] ?? ''));
    }
    if ($input['name']==='') {
        $errors[] = 'Bitte geben Sie Ihren Namen an.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
    }
    if ($input['subject']==='') {
        $errors[] = 'Bitte geben Sie einen Betreff an.';
    }
    if ($input['message']==='') {
        $errors[] = 'Bitte geben Sie eine Nachricht ein.';
    }

    // 4) Wenn alles OK: ID + Mailversand
    if (empty($errors)) {
        $contactId = strtoupper(bin2hex(random_bytes(8)));

        require __DIR__ . '/../includes/mailing.inc.php';

        // Mail an Team
        $subjectTeam = "Kontaktformular ({$contactId}): " . $input['subject'];
        $htmlTeam    = "
            <p><strong>Kontakt-ID:</strong> {$contactId}</p>
            <p><strong>Name:</strong> " . htmlspecialchars($input['name']) . "</p>
            <p><strong>E-Mail:</strong> " . htmlspecialchars($input['email']) . "</p>
            <p><strong>Nachricht:</strong><br>" .
              nl2br(htmlspecialchars($input['message'])) . "
            </p>
        ";
        sendMail($config['contact_email'], $config['app_name'], $subjectTeam, $htmlTeam, null, $log);

        // Auto-Reply
        $subjectUser = "Ihre Anfrage ({$contactId}) bei {$config['app_name']}";
        $htmlUser    = "
            <p>Hallo " . htmlspecialchars($input['name']) . ",</p>
            <p>vielen Dank für Ihre Nachricht. Ihre Anfrage-ID lautet <strong>{$contactId}</strong>. 
               Wir melden uns binnen 24 Stunden zurück.</p>
            <p>Servicezeiten: Mo–Fr, 9 – 17 Uhr.</p>
            <p>Herzliche Grüße,<br>{$config['app_name']}-Team</p>
        ";
        sendMail($input['email'], $input['name'], $subjectUser, $htmlUser, null, $log);

        $success = true;
        // Formular zurücksetzen
        $input = ['name'=>'','email'=>'','subject'=>'','message'=>''];
    }
}

$smarty->assign('errors',    $errors);
$smarty->assign('success',   $success);
$smarty->assign('input',     $input);
$smarty->assign('contactId',$contactId);

$smarty->display('contact.tpl');
