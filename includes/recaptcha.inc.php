<?php
declare(strict_types=1);

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
 * Gibt das Hidden-Input-Feld für das Token zurück.
 */
function recaptcha_get_hidden_field(): string
{
    return '<input type="hidden" name="recaptcha_token" id="recaptcha_token">';
}

/**
 * Schreibt einen Logeintrag in das reCAPTCHA-Logfile.
 */
function recaptcha_write_log(string $line, string $logFile): void
{
    file_put_contents($logFile, date('c') . ' ' . $line . "\n", FILE_APPEND);
}

/**
 * Protokolliert ein reCAPTCHA-Resultat in der Datenbank.
 */
function recaptcha_log(PDO $pdo, string $token, array $resp, ?string $reason): void
{
    $sql = <<<SQL
INSERT INTO captcha_log (token, success, score, action, hostname, error_reason)
VALUES (:token, :success, :score, :action, :hostname, :error_reason)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':token'        => $token,
        ':success'      => !empty($resp['success']) ? 1 : 0,
        ':score'        => $resp['score'] ?? null,
        ':action'       => $resp['action'] ?? null,
        ':hostname'     => $resp['hostname'] ?? null,
        ':error_reason' => $reason
    ]);
}

/**
 * Prüft reCAPTCHA v3-Token gegen Googles API.
 *
 * @param PDO $pdo
 * @param string $token Token vom Client
 * @param string $secret Dein geheimer Schlüssel
 * @param float $minScore Mindestscore (z.B. 0.5)
 * @param array $validActions Erlaubte Actions wie ['register','login']
 * @param string|null $logFile Optionales Logfile für Debug
 * @return bool true wenn gültig
 */
function recaptcha_verify(
    PDO $pdo,
    string $token,
    string $secret,
    float $minScore = 0.5,
    array $validActions = ['register', 'login', 'contact'],
    ?string $logFile = null
): bool {
    $errorReason = null;

    if (empty($token)) {
        $errorReason = 'no_token';
        recaptcha_log($pdo, $token, [], $errorReason);
        if ($logFile) recaptcha_write_log("[no_token] token='(leer)'", $logFile);
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);

    $resp = json_decode((string)@file_get_contents($url), true) ?: [];

    if ($logFile) {
        recaptcha_write_log("[API] Response: " . print_r($resp, true), $logFile);
    }

    if (empty($resp['success'])) {
        $errorReason = 'success=false';
    } elseif (($resp['score'] ?? 0) < $minScore) {
        $errorReason = 'low_score:' . round((float)$resp['score'], 2);
    } elseif (!in_array($resp['action'] ?? '', $validActions, true)) {
        $errorReason = 'wrong_action:' . ($resp['action'] ?? '(none)');
    }

    recaptcha_log($pdo, $token, $resp, $errorReason);

    if ($logFile) {
        recaptcha_write_log(
            $errorReason
                ? "[FAIL] reason={$errorReason}"
                : "[OK] success, score=" . ($resp['score'] ?? 'null'),
            $logFile
        );
    }

    return $errorReason === null;
}

/**
 * Vereinfachter Wrapper für recaptcha_verify mit globaler $config-Nutzung.
 */
function recaptcha_verify_auto(PDO $pdo, string $token): bool
{
    global $config;

    return recaptcha_verify(
        $pdo,
        $token,
        $config['recaptcha']['secret_key'],
        $config['recaptcha']['min_score'],
        $config['recaptcha']['actions'],
        $config['recaptcha']['log_file']
    );
}
