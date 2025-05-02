<?php
declare(strict_types=1);

use ParagonIE\Halite\KeyFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;
use Monolog\Logger;  // Monolog importieren

/**
 * Erzeugt ein Verifikationstoken, speichert es in der DB und
 * versendet die Verifizierungs-E-Mail per PHPMailer.
 *
 * @param PDO     $pdo
 * @param string  $username
 * @param string  $email
 * @param string  $host
 * @param Logger  $log      Dein Monolog-Logger
 *
 * @throws RuntimeException
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
    $link = sprintf('https://%s/verify.php?token=%s', $host, urlencode($token));

    // 4) PHPMailer einrichten
    $mail = new PHPMailer(true);
    try {
        // — Charset & Encoding für Umlaute —
        $mail->CharSet   = 'UTF-8';  
        $mail->Encoding  = PHPMailer::ENCODING_QUOTED_PRINTABLE;
        // SMTP‐Debug via Monolog
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function(string $str, int $level) use ($log) {
            $log->debug("PHPMailer [level {$level}]: {$str}");
        };

        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST']      ?? getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']      ?? getenv('SMTP_USER');
        $mail->Password   = $_ENV['SMTP_PASS']      ?? getenv('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = intval($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587);

        $mail->setFrom(
            $_ENV['SMTP_FROM']      ?? getenv('SMTP_FROM'),
            $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME')
        );
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Bitte bestätige deine E-Mail-Adresse';
        $mail->Body    = "
            <p>Hallo <strong>{$username}</strong>,</p>
            <p>klicke auf diesen Link, um deine E-Mail zu bestätigen:</p>
            <p><a href=\"{$link}\">E-Mail-Adresse jetzt bestätigen</a></p>
            <p>Viele Grüße,<br>StudyHub-Team</p>
        ";
        $mail->AltBody = "Hallo {$username},\n"
                       . "Bestätige deine E-Mail mit:\n{$link}\n\nStudyHub-Team";

        $mail->send();
    } catch (MailException $e) {
        throw new RuntimeException(
            'Verifizierungs-Mail konnte nicht gesendet werden: ' . $mail->ErrorInfo
        );
    }
}
