<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../includes/csrf.inc.php';
require_once __DIR__ . '/../includes/mailing.inc.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Repository/ContactRequestRepository.php';

// Admin-/Mod-Schutz
if (empty($_SESSION['user_id'])) {
    $reason = 'Nicht eingeloggt.';
    handle_error(401, $reason, 'both');
}
if (!in_array($_SESSION['role'] ?? '', ['admin', 'mod'], true)) {
    $reason = 'Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen.';
    handle_error(403, $reason, 'both');
}

$db = new Database();
$contactRequestRepository = new ContactRequestRepository($db);

// Flash-Messages
$_SESSION['flash'] = null;

// Antwort senden
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    validate_csrf_token();
    $contactId = trim($_POST['reply_contact_id']);
    $replyText = trim($_POST['reply_text'] ?? '');

    if ($replyText !== '') {
        // This is not ideal, but we'll refactor it later
        $requests = $contactRequestRepository->getFilteredContactRequests(['contact_id' => $contactId]);
        if (!empty($requests)) {
            $contact = $requests[0];
            $safeName  = htmlspecialchars($contact['name'], ENT_QUOTES);
            $safeReply = nl2br(htmlspecialchars($replyText, ENT_QUOTES));
            $subject   = "Antwort auf deine Kontaktanfrage #{$contactId} bei StudyHub";
            $body      = "<p>Hallo {$safeName},</p>" .
                         '<p>wir haben deine Kontaktanfrage erhalten und möchten dir wie folgt antworten:</p>' .
                         "<blockquote>{$safeReply}</blockquote>" .
                         '<p>Viele Grüße,<br>Dein StudyHub-Team</p>';
            try {
                sendMail($contact['email'], $contact['name'], $subject, $body);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Antwort erfolgreich versendet.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Fehler beim Senden der Antwort.'];
                error_log('Antwortfehler: ' . $e->getMessage());
            }
        }
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Antworttext darf nicht leer sein.'];
    }

    header('Location: contact_request.php');
    exit;
}

// Statuswechsel
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['status_contact_id'], $_POST['new_status'])
) {
    validate_csrf_token();
    $contactId   = trim($_POST['status_contact_id']);
    $newStatus   = trim($_POST['new_status']);
    $closeReply  = trim($_POST['close_reply_text'] ?? '');
    $validStates = ['offen', 'in_bearbeitung', 'geschlossen'];

    if (in_array($newStatus, $validStates, true)) {
        if ($newStatus === 'geschlossen' && $closeReply === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Antworttext wird zum Schließen benötigt.'];
            header('Location: contact_request.php');
            exit;
        }

        $db->execute("UPDATE contact_requests SET status = ? WHERE contact_id = ?", [$newStatus, $contactId]);
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Status wurde aktualisiert.'];

        if ($newStatus === 'geschlossen') {
            $requests = $contactRequestRepository->getFilteredContactRequests(['contact_id' => $contactId]);
            if (!empty($requests)) {
                $contact = $requests[0];
                $safeName  = htmlspecialchars($contact['name'], ENT_QUOTES);
                $safeReply = nl2br(htmlspecialchars($closeReply, ENT_QUOTES));
                $subject   = "Kontaktanfrage #{$contactId} abgeschlossen";
                $body      = "<p>Hallo {$safeName},</p><p>{$safeReply}</p><p>Viele Grüße,<br>Dein StudyHub-Team</p>";
                try {
                    sendMail($contact['email'], $contact['name'], $subject, $body);
                } catch (Exception $e) {
                    error_log('Close contact request mail failed: ' . $e->getMessage());
                }
            }
        }
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ungültiger Statuswert.'];
    }

    header('Location: contact_request.php');
    exit;
}

// Anfrage löschen
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_contact_id'])
) {
    validate_csrf_token();
    $delId = trim($_POST['delete_contact_id']);
    $contactRequestRepository->deleteContactRequest($delId);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kontaktanfrage gelöscht.'];
    header('Location: contact_request.php');
    exit;
}

// Filter vorbereiten
$filters = [
    'name'    => trim($_GET['name'] ?? ''),
    'email'   => trim($_GET['email'] ?? ''),
    'subject' => trim($_GET['subject'] ?? ''),
    'from'    => trim($_GET['from'] ?? ''),
    'to'      => trim($_GET['to'] ?? ''),
];

// CSV-Export
$requests = $contactRequestRepository->getFilteredContactRequests($filters);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contact_requests.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'E-Mail', 'Betreff', 'Nachricht', 'IP-Adresse', 'Datum', 'Status']);

    foreach ($requests as $r) {
        fputcsv($output, [
            $r['name'], $r['email'], $r['subject'], $r['message'],
            $r['ip_address'] ?? '', $r['created_at'], $r['status']
        ]);
    }
    fclose($output);
    exit;
}

// Ausgabe für Template
$smarty->assign([
    'contact_requests' => $requests,
    'filters'          => $filters,
    'flash'            => $_SESSION['flash'],
    'isAdmin'          => ($_SESSION['role'] ?? '') === 'admin',
    'isMod'            => ($_SESSION['role'] ?? '') === 'mod',
    'username'         => $_SESSION['username'] ?? '',
]);

unset($_SESSION['flash']);
$smarty->display('contact_requests.tpl');
