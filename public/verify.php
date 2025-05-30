<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

$monolog = getLogger('verify');
$log     = new MonologLoggerAdapter($monolog);

$token = trim((string)($_GET['token'] ?? ''));

$verifyData = [
    'alertType'   => 'danger',
    'message'     => 'Ungültiger Verifizierungslink.',
    'showButton'  => true,
    'buttonText'  => 'Zur Startseite',
    'buttonLink'  => 'index.php',
];

try {
    if ($token === '') {
        throw new RuntimeException('Token fehlt');
    }

    // 1) Hole User- und Token-Daten
    $user = DbFunctions::fetchVerificationUser($token);
    $log->info('Verifizierungstoken geprüft', ['token' => $token]);

    if (!$user) {
        $log->warning('Verifizierungstoken nicht gefunden', ['token' => $token]);
        throw new RuntimeException('Token ungültig oder abgelaufen.');
    }

    // 2) Bereits verifiziert?
    if ((bool)$user['is_verified']) {
        $verifyData['alertType']  = 'info';
        $verifyData['message']    = 'Deine E-Mail-Adresse wurde bereits bestätigt.';
        $verifyData['buttonText'] = 'Jetzt einloggen';
        $verifyData['buttonLink'] = 'index.php#loginModal';
    } else {
        // 3) Verifizieren
        DbFunctions::verifyUser((int)$user['id']);
        $log->info('Nutzer verifiziert', ['user_id' => $user['id']]);

        // 4) Token löschen
        DbFunctions::deleteVerificationToken((int)$user['id']);
        $log->info('Verifizierungstoken gelöscht', ['user_id' => $user['id']]);

        $verifyData['alertType']  = 'success';
        $verifyData['message']    = 'Deine E-Mail wurde erfolgreich verifiziert!';
        $verifyData['buttonText'] = 'Jetzt einloggen';
        $verifyData['buttonLink'] = 'index.php#show=login';
    }

} catch (Throwable $e) {
    $log->error('Verifizierung fehlgeschlagen', [
        'error' => $e->getMessage(),
        'token' => $token,
    ]);

    $verifyData['alertType']  = 'danger';
    $verifyData['message']    = 'Ein interner Fehler ist aufgetreten. Bitte später erneut versuchen.';
    $verifyData['buttonText'] = 'Zur Startseite';
    $verifyData['buttonLink'] = 'index.php';
}

// Übergabe an Template
$smarty->assign($verifyData);
$smarty->display('verify.tpl');
