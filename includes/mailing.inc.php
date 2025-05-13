<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;

/**
 * Gibt eine konfigurierte PHPMailer-Instanz zurück.
 */
function getMailer(): PHPMailer
{
    global $config;

    $mail = new PHPMailer(true);

    // Zeichensatz
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;


    // SMTP-Konfiguration
    $mail->isSMTP();
    $mail->Host       = $config['mail']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['mail']['user'];
    $mail->Password   = $config['mail']['pass'];
    $mail->SMTPSecure = $config['mail']['encryption'];
    $mail->Port       = $config['mail']['port'];

    // Absender
    $mail->setFrom($config['mail']['from'], $config['mail']['from_name']);

    return $mail;
}

/**
 * Sendet eine E-Mail.
 */
function sendMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    ?string $altBody = null,
): void {
    $mail = getMailer();

    try {
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?? strip_tags(preg_replace('/<(br|\/p)>/i', "\n", $htmlBody));

        $mail->send();
    } catch (MailException $e) {
        throw new RuntimeException(
            'E-Mail-Versand fehlgeschlagen: ' . $mail->ErrorInfo . ' | ' . $e->getMessage()
        );
    }
}
