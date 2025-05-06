<?php
declare(strict_types=1);

use PDO;

// Pfad zum Logfile im Projekt-Logs-Verzeichnis
if (!defined('RECAPTCHA_LOGFILE')) {
    define('RECAPTCHA_LOGFILE', __DIR__ . '/../logs/recaptcha.log');
}

/**
 * Gibt den <script>-Tag zurück, um reCAPTCHA v3 zu laden.
 */
function recaptcha_get_script(string $siteKey): string
{
    return '<script src="https://www.google.com/recaptcha/api.js?render='
         . htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8')
         . '"></script>';
}

/**
 * Gibt das Hidden-Input-Feld aus, in das das Token geschrieben wird.
 */
function recaptcha_get_hidden_field(): string
{
    return '<input type="hidden" name="recaptcha_token" id="recaptcha_token">';
}

/**
 * Protokolliert einen reCAPTCHA-Check in der Datenbank.
 */
function recaptcha_log(PDO $pdo, string $token, array $resp, ?string $errorReason): void
{
    $sql = <<<SQL
INSERT INTO captcha_log
  (token, success, score, action, hostname, error_reason)
VALUES
  (:token, :success, :score, :action, :hostname, :error_reason)
SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':token'        => $token,
        // Boolean als Integer übergeben (1 = true, 0 = false)
        ':success'      => !empty($resp['success']) ? 1 : 0,
        ':score'        => $resp['score']    ?? null,
        ':action'       => $resp['action']   ?? null,
        ':hostname'     => $resp['hostname'] ?? null,
        ':error_reason' => $errorReason
    ]);
}

/**
 * Verifiziert reCAPTCHA v3, loggt das Ergebnis in DB und in eine Datei.
 *
 * @param PDO    $pdo       Deine PDO-Verbindung
 * @param string $token     Der vom Client gesendete Token
 * @param string $secret    Dein reCAPTCHA-Secret
 * @param float  $minScore  Mindest-Score (default 0.5)
 * @return bool
 */
function recaptcha_verify(PDO $pdo, string $token, string $secret, float $minScore = 0.5): bool
{
    $errorReason = null;

    // 1) Kein Token?
    if (empty($token)) {
        $errorReason = 'no_token';
        recaptcha_log($pdo, $token, [], $errorReason);
        file_put_contents(RECAPTCHA_LOGFILE,
            date('c') . " [no_token] token='(leer)'\n",
            FILE_APPEND
        );
        return false;
    }

    // 2) Anfrage an Google
    $url  = 'https://www.google.com/recaptcha/api/siteverify?secret='
          . urlencode($secret)
          . '&response=' . urlencode($token)
          . '&remoteip=' . urlencode($_SERVER['REMOTE_ADDR']);
    $resp = json_decode((string)@file_get_contents($url), true) ?: [];

    // 3) In Datei loggen
    file_put_contents(RECAPTCHA_LOGFILE,
        date('c') . " [API] URL: {$url}\n" .
        date('c') . " [API] Response: " . print_r($resp, true) . "\n",
        FILE_APPEND
    );

    // 4) Gründe für Misserfolg bestimmen
    if (empty($resp['success'])) {
        $errorReason = 'success!=' . ($resp['success'] ?? 'null');
    } elseif (($resp['score'] ?? 0) < $minScore) {
        $errorReason = 'low_score:' . round((float)$resp['score'], 2);
    } elseif (!in_array($resp['action'] ?? '', ['contact','login','register'], true)) {
        $errorReason = 'wrong_action:' . ($resp['action'] ?? '(none)');
    }

    // 5) In DB loggen
    recaptcha_log($pdo, $token, $resp, $errorReason);

    // 6) Ergebnis ebenfalls in Datei loggen
    if ($errorReason !== null) {
        file_put_contents(RECAPTCHA_LOGFILE,
            date('c') . " [FAIL] reason={$errorReason}\n",
            FILE_APPEND
        );
    } else {
        file_put_contents(RECAPTCHA_LOGFILE,
            date('c') . " [OK] success, score=" . ($resp['score'] ?? 'null') . "\n",
            FILE_APPEND
        );
    }

    return $errorReason === null;
}
