<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;
use Monolog\Logger;

/**
 * Liefert einen vor­konfigurierten PHPMailer-Instanz.
 *
 * @param Logger|null $log Optional: Monolog-Logger für SMTP-Debug
 * @return PHPMailer
 */
function getMailer(?Logger $log = null): PHPMailer
{
    $mail = new PHPMailer(true);

    // Charset & Encoding
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;

    // Optional SMTP-Debug in Monolog loggen
    if ($log) {
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function(string $str, int $level) use ($log) {
            $log->debug("PHPMailer [level {$level}]: {$str}");
        };
    }

    // SMTP-Konfiguration
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST']      ?? getenv('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER']      ?? getenv('SMTP_USER');
    $mail->Password   = $_ENV['SMTP_PASS']      ?? getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = intval($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587);

    // Absender
    $mail->setFrom(
        $_ENV['SMTP_FROM']      ?? getenv('SMTP_FROM'),
        $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME')
    );

    return $mail;
}

/**
 * Sendet eine E-Mail.
 *
 * @param string      $toEmail   Empfänger-Adresse
 * @param string      $toName    Empfänger-Name
 * @param string      $subject   Betreff
 * @param string      $htmlBody  HTML-Inhalt
 * @param string|null $altBody   Plain-Text-Alternative
 * @param Logger|null $log       Optional: Monolog-Logger
 *
 * @throws RuntimeException Bei Fehlern im Versand
 */
function sendMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    ?string $altBody = null,
    ?Logger $log = null
): void {
    $mail = getMailer($log);

    try {
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?? strip_tags(str_replace(['<br>', '<p>'], ["\n", "\n\n"], $htmlBody));
        $mail->send();
    } catch (MailException $e) {
        throw new RuntimeException('E-Mail konnte nicht gesendet werden: ' . $mail->ErrorInfo);
    }
}
