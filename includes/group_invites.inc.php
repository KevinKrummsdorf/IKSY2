<?php
declare(strict_types=1);

require_once __DIR__ . '/mailing.inc.php';

/**
 * Versendet eine Einladung zu einer Lerngruppe per E-Mail.
 */
function sendGroupInviteEmail(string $toEmail, string $toName, string $groupName, string $inviterName, string $token): void
{
    global $config;

    $siteUrl = $config['site_url'] ?? $config['base_url'] ?? '';
    if (!$siteUrl) {
        throw new RuntimeException('site_url nicht gesetzt');
    }

    $link = rtrim($siteUrl, '/') . '/join_group.php?token=' . urlencode($token);

    $subject = 'Einladung zur Lerngruppe ' . $groupName;
    $groupEsc = htmlspecialchars($groupName, ENT_QUOTES);
    $inviterEsc = htmlspecialchars($inviterName, ENT_QUOTES);

    $htmlBody = "<p>Hallo <strong>{$toName}</strong>,</p>" .
        "<p>{$inviterEsc} hat dich zur Lerngruppe <strong>{$groupEsc}</strong> eingeladen.</p>" .
        '<p>Klicke auf folgenden Link, um beizutreten (gültig 48 Stunden):</p>' .
        "<p><a href=\"{$link}\">Jetzt beitreten</a></p>" .
        '<p>Viele Grüße,<br>StudyHub-Team</p>';

    $altBody = "Hallo {$toName},\n\n" .
        "$inviterName hat dich zur Lerngruppe '{$groupName}' eingeladen.\n" .
        "Nutze folgenden Link, um beizutreten:\n{$link}\n\n" .
        "Viele Grüße,\nStudyHub-Team";

    sendMail($toEmail, $toName, $subject, $htmlBody, $altBody);
}
