<?php
declare(strict_types=1);

require_once __DIR__ . '/mailing.inc.php';
use ParagonIE\Halite\KeyFactory;
use Monolog\Logger;

/**
 * Erzeugt Token, speichert es in der DB und
 * verschickt die Verifizierungs-Mail.
 */
function sendVerificationEmail(
    PDO    $pdo,
    string $username,
    string $email,
    string $host,
    Logger $log
): void {
    // 1) Token erzeugen
    $encryptionKey = KeyFactory::generateEncryptionKey();
    $token         = KeyFactory::export($encryptionKey)->getString();

    // 2) In DB speichern
    $stmt = $pdo->prepare(
        'UPDATE users
            SET verification_token = ?, verification_sent_at = NOW()
          WHERE username = ?'
    );
    if (!$stmt->execute([$token, $username])) {
        throw new RuntimeException('Fehler beim Speichern des Verifikationstokens.');
    }

    // 3) Link bauen
    $link      = sprintf('https://%s/verify.php?token=%s', $host, urlencode($token));
    $subject   = 'Bitte bestätige deine E-Mail-Adresse';
    $htmlBody  = "
        <p>Hallo <strong>{$username}</strong>,</p>
        <p>klicke auf diesen Link, um deine E-Mail zu bestätigen:</p>
        <p><a href=\"{$link}\">E-Mail-Adresse jetzt bestätigen</a></p>
        <p>Viele Grüße,<br>StudyHub-Team</p>
    ";
    $altBody   = "Hallo {$username},\n"
               . "Bestätige deine E-Mail mit:\n{$link}\n\nStudyHub-Team";

    // 4) Mail versenden
    sendMail($email, $username, $subject, $htmlBody, $altBody, $log);
}
