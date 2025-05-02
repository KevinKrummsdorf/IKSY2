<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Standard-Header
header('Content-Type: text/html; charset=utf-8');

// 2) Autoloader & Umgebungsvariablen
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 2.1) Smarty initialisieren
use Smarty\Smarty;
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

require_once __DIR__ . '/../includes/db.inc.php';

try {
    // 4) Token aus URL auslesen
    $token = trim((string)($_GET['token'] ?? ''));
    if ($token === '') {
        http_response_code(400);
        $smarty->assign([
            'alertType'   => 'danger',
            'message'     => 'Ungültiger Verifizierungslink.',
            'showButton'  => true,
            'buttonText'  => 'Zur Startseite',
            'buttonLink'  => 'index.php',
        ]);
        $smarty->display('verify.tpl');
        exit;
    }

    // 5) DB-Verbindung
    $pdo = DbFunctions::db_connect();

    // 6) User mit diesem Token suchen
    $stmt = $pdo->prepare(
        'SELECT id, is_verified
           FROM users
          WHERE verification_token = :token
          LIMIT 1'
    );
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    // 7) Link prüfen
    if (!$user) {
        // Kein Treffer → Button „Zur Startseite“
        $smarty->assign([
            'alertType'   => 'warning',
            'message'     => 'Dieser Verifizierungslink ist ungültig oder abgelaufen.',
            'showButton'  => true,
            'buttonText'  => 'Zur Startseite',
            'buttonLink'  => 'index.php',
        ]);
        $smarty->display('verify.tpl');
        exit;
    }

    if ((bool)$user['is_verified']) {
        // Bereits verifiziert → Login-Button
        $smarty->assign([
            'alertType'   => 'info',
            'message'     => 'Deine E-Mail-Adresse wurde bereits bestätigt.',
            'showButton'  => true,
            'buttonText'  => 'Jetzt einloggen',
            'buttonLink'  => 'index.php#loginModal',
        ]);
        $smarty->display('verify.tpl');
        exit;
    }

    // 8) Verifikation durchführen
    $upd = $pdo->prepare(
        'UPDATE users
            SET is_verified = TRUE,
                verification_token = NULL
          WHERE id = :id'
    );
    $upd->execute([':id' => (int)$user['id']]);

    // 9) Erfolgsseite → Login-Button
    $smarty->assign([
        'alertType'   => 'success',
        'message'     => 'Deine E-Mail wurde erfolgreich verifiziert!',
        'showButton'  => true,
        'buttonText'  => 'Jetzt einloggen',
        'buttonLink'  => 'index.php#loginModal',
    ]);
    $smarty->display('verify.tpl');
    exit;

} catch (\Throwable $e) {
    // 10) Fehler protokollieren, aber generische Meldung ausgeben
    error_log('Verify-Error: ' . $e->getMessage());
    http_response_code(500);
    $smarty->assign([
        'alertType'   => 'danger',
        'message'     => 'Ein interner Fehler ist aufgetreten. Bitte später erneut versuchen.',
        'showButton'  => true,
        'buttonText'  => 'Zur Startseite',
        'buttonLink'  => 'index.php',
    ]);
    $smarty->display('verify.tpl');
    exit;
}
