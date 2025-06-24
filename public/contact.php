<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Models/Database.php';
require_once __DIR__ . '/../src/Models/ContactModel.php';
require_once __DIR__ . '/../src/Controllers/ContactController.php';

// Initialisierung
$errors    = [];
$success   = false;
$input     = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$contactId = null;

$log = LoggerFactory::get('contact');
$ip      = getClientIp();
$maskedIp= maskIp($ip);

// DB-Verbindung
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // reCAPTCHA prüfen
    $token = $_POST['recaptcha_token'] ?? '';
    if (!recaptcha_verify_auto($pdo, $token)) {
        $log->warning('reCAPTCHA-Validierung fehlgeschlagen', [
            'token' => $token,
            'ip'    => $maskedIp,
        ]);
        $errors[] = 'reCAPTCHA-Validierung fehlgeschlagen. Bitte erneut versuchen.';
    }

    foreach (array_keys($input) as $f) {
        $input[$f] = trim((string)($_POST[$f] ?? ''));
    }

    if (empty($errors)) {
        $controller = new ContactController();
        $result = $controller->submit($input, $maskedIp, $_SERVER['HTTP_USER_AGENT'] ?? null);
        if (!$result['success']) {
            $errors = $result['errors'];
        } else {
            $contactId = $result['contactId'];
            $log->info('Kontaktanfrage gespeichert', [
                'contact_id' => $contactId,
                'ip'         => $maskedIp,
            ]);

        // Mail an Team
        try {
            $subjectTeam = "Kontaktformular ({$contactId}): " . $input['subject'];
            $htmlTeam = '<p><strong>Kontakt-ID:</strong> ' . $contactId . '</p>' .
                        '<p><strong>Name:</strong> ' . htmlspecialchars($input['name'], ENT_QUOTES) . '</p>' .
                        '<p><strong>E-Mail:</strong> ' . htmlspecialchars($input['email'], ENT_QUOTES) . '</p>' .
                        '<p><strong>Nachricht:</strong><br>' . nl2br(htmlspecialchars($input['message'], ENT_QUOTES)) . '</p>';
            sendMail($config['mail']['contact_email'], $config['app_name'], $subjectTeam, $htmlTeam);
            $log->info('E-Mail an Team gesendet', ['contact_id' => $contactId, 'ip' => $maskedIp]);
        } catch (\Throwable $e) {
            $log->error('Fehler beim Senden der Team-Mail: ' . $e->getMessage(), ['contact_id' => $contactId]);
            $errors[] = 'Leider konnte die Benachrichtigung an unser Team nicht versendet werden.';
        }

        // Auto-Reply
        try {
            $subjectUser = "Ihre Anfrage ({$contactId}) bei {$config['app_name']}";
            $htmlUser = '<p>Hallo ' . htmlspecialchars($input['name'], ENT_QUOTES) . ',</p>' .
                        '<p>vielen Dank für Ihre Nachricht. Ihre Anfrage-ID lautet <strong>' . $contactId . '</strong>. Wir melden uns binnen 24 Stunden zurück.</p>' .
                        '<p>Servicezeiten: Mo–Fr, 9 – 17 Uhr.</p>' .
                        '<p>Herzliche Grüße,<br>' . $config['app_name'] . '-Team</p>';
            sendMail($input['email'], $input['name'], $subjectUser, $htmlUser);
            $log->info('Auto-Reply gesendet', ['contact_id' => $contactId, 'ip' => $maskedIp]);
        } catch (\Throwable $e) {
            $log->error('Fehler beim Senden der Auto-Reply: ' . $e->getMessage(), ['contact_id' => $contactId]);
            $errors[] = 'Deine Bestätigungs-E-Mail konnte nicht gesendet werden.';
        }

        $success = true;
        $input   = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        }
    }
}

// An Smarty übergeben
$smarty->assign('errors',    $errors);
$smarty->assign('success',   $success);
$smarty->assign('input',     $input);
$smarty->assign('contactId', $contactId);
// reCAPTCHA-Site-Key für das Template (korrekter Pfad)
$smarty->assign('recaptcha_site_key', $config['recaptcha']['site_key']);

// Rendern
$smarty->display('contact.tpl');
exit;
