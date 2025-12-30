<?php
declare(strict_types=1);

// Session wird bereits in config.inc.php gestartet

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/ContactRequestRepository.php';

// Initialisierung
$errors    = [];
$success   = false;
$input     = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$contactId = null;

$ip      = getClientIp();
$maskedIp= maskIp($ip);

// DB-Verbindung
$db = new Database();
$contactRequestRepository = new ContactRequestRepository($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // reCAPTCHA prüfen
    $token = $_POST['recaptcha_token'] ?? '';
    if (!recaptcha_verify_auto($db, $token)) {

        $errors[] = 'reCAPTCHA-Validierung fehlgeschlagen. Bitte erneut versuchen.';
    }

    // Eingaben prüfen
    foreach (array_keys($input) as $f) {
        $input[$f] = trim((string)($_POST[$f] ?? ''));
    }

    if ($input['name'] === '') {
        $errors[] = 'Bitte geben Sie Ihren Namen an.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
    }
    if ($input['subject'] === '') {
        $errors[] = 'Bitte geben Sie einen Betreff an.';
    }
    if ($input['message'] === '') {
        $errors[] = 'Bitte geben Sie eine Nachricht ein.';
    }

    // Wenn keine Fehler, speichern & versenden
    if (empty($errors)) {
        $contactId = 'CF' . strtoupper(bin2hex(random_bytes(4)));

        // In DB speichern
        $contactRequestRepository->createContactRequest(
            $contactId,
            $input['name'],
            $input['email'],
            $input['subject'],
            $input['message'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        // Mail an Team
        try {
            $subjectTeam = "Kontaktformular ({$contactId}): " . $input['subject'];
            $htmlTeam = '<p><strong>Kontakt-ID:</strong> ' . $contactId . '</p>' .
                        '<p><strong>Name:</strong> ' . htmlspecialchars($input['name'], ENT_QUOTES) . '</p>' .
                        '<p><strong>E-Mail:</strong> ' . htmlspecialchars($input['email'], ENT_QUOTES) . '</p>' .
                        '<p><strong>Nachricht:</strong><br>' . nl2br(htmlspecialchars($input['message'], ENT_QUOTES)) . '</p>';
            sendMail($config['mail']['contact_email'], $config['app_name'], $subjectTeam, $htmlTeam);
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            $errors[] = 'Deine Bestätigungs-E-Mail konnte nicht gesendet werden.';
        }

        $success = true;
        $input   = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
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
