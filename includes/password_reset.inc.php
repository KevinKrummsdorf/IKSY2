<?php
declare(strict_types=1);

/**
 * Versendet eine E-Mail zum Zurücksetzen des Passworts.
 */
function sendPasswordResetEmail(
    PDO $pdo,
    int $userId,
    string $username,
    string $email,
    string $token
): void {
    global $config;

    $siteUrl = $config['site_url'] ?? $config['base_url'] ?? '';
    if (!$siteUrl) {
        throw new RuntimeException('Keine site_url oder base_url vorhanden.');
    }
    $link = rtrim($siteUrl, '/') . '/reset_password.php?token=' . urlencode($token);

    $subject = $config['mail']['reset_subject'] ?? 'Passwort zurücksetzen';

    $htmlBody = "<p>Hallo <strong>{$username}</strong>,</p>
        <p>klicke auf diesen Link, um dein Passwort zurückzusetzen:</p>
        <p><a href=\"{$link}\">Passwort jetzt zurücksetzen</a></p>
        <p>Viele Grüße,<br>StudyHub-Team</p>";

    $altBody = "Hallo {$username},\n\n" .
        "bitte setze dein Passwort über diesen Link zurück:\n{$link}\n\n" .
        "Viele Grüße,\nStudyHub-Team";

    sendMail($email, $username, $subject, $htmlBody, $altBody);
}

function sendPasswordResetSuccessEmail (
    PDO $pdo,
    int $userId,
    string $username,
    string $email,
) : void 
{
    global $config;

    $siteUrl = $config['site_url'] ?? $config['base_url'] ?? '';
    if (!$siteUrl) {
        throw new RuntimeException('Keine site_url oder base_url vorhanden.');
    }

    $link = rtrim($siteUrl, '/') . 'index.php?show=login';
    $subject ='Passwort wurde erfolgreich zurückgesetzt';

    $htmlBody = "<p>Hallo <strong>{$username}</strong>,</p>
        <p>dein Passwort wurde erfolgreich zurückgesetzt. Du kannst dich nun mit deinem neuen Passwort einloggen.</p>
        <p><a href=\"{$link}\">Jetzt einloggen</a></p>
        <p>\nWenn du das Zurücksetzten des Passworts nicht veranlasst hast, wende dich bitte unverzüglich an den Support.\n</p>
        <p>Viele Grüße,<br>StudyHub-Team</p>";

    $altBody = "Hallo {$username},\n\n" .
        "dein Passwort wurde erfolgreich zurückgesetzt" .
        "Viele Grüße,\nStudyHub-Team";

    sendMail($email, $username, $subject, $htmlBody, $altBody);
}