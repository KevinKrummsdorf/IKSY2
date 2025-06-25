<?php
declare(strict_types=1);

use ParagonIE\Halite\KeyFactory;

/**
 * Sendet eine E-Mail zur Verifizierung der Adresse und speichert den Token.
 *
 * @param PDO    $pdo      Datenbankverbindung
 * @param int    $userId   Benutzer-ID
 * @param string $username Anzeigename
 * @param string $email    Empfängeradresse
 *
 * @throws RuntimeException
 */
function sendVerificationEmail(
    PDO $pdo,
    int $userId,
    string $username,
    string $email,
): void {
    global $config;

    // 1. Token erzeugen (zufällig & sicher)
    $encryptionKey = KeyFactory::generateEncryptionKey();
    $token         = KeyFactory::export($encryptionKey)->getString();

    // 2. In verification_tokens speichern (ersetzen, falls vorhanden)
    $stmt = $pdo->prepare('
        INSERT INTO verification_tokens (user_id, verification_token, verification_sent_at)
        VALUES (:user_id, :token, NOW())
        ON DUPLICATE KEY UPDATE
            verification_token = VALUES(verification_token),
            verification_sent_at = VALUES(verification_sent_at)
    ');

    if (!$stmt->execute([
        ':user_id' => $userId,
        ':token'   => $token,
    ])) {
        throw new RuntimeException('Fehler beim Speichern des Verifikationstokens.');
    }

    // 3. Link erstellen
    $siteUrl = $config['site_url'] ?? $config['base_url'] ?? '';
    if (!$siteUrl) {
        throw new RuntimeException('Kein site_url oder base_url in der Konfiguration vorhanden.');
    }
    $link = rtrim($siteUrl, '/') . '/verify.php?token=' . urlencode($token);


    // 4. E-Mail-Inhalt vorbereiten
    $subject = $config['mail']['verify_subject'] ?? 'Bitte bestätige deine E-Mail-Adresse';

    $htmlBody = "
        <p>Hallo <strong>{$username}</strong>,</p>
        <p>klicke auf diesen Link, um deine E-Mail zu bestätigen:</p>
        <p><a href=\"{$link}\">E-Mail-Adresse jetzt bestätigen</a></p>
        <p>Viele Grüße,<br>StudyHub-Team</p>
    ";

    $altBody = "Hallo {$username},\n\n"
             . "bitte bestätige deine E-Mail über diesen Link:\n{$link}\n\n"
             . "Viele Grüße,\nStudyHub-Team";

    // 5. Mail versenden
    sendMail($email, $username, $subject, $htmlBody, $altBody,);
}
