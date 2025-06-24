<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/HomeController.php';

use App\Router;
use App\HomeController;

$router = new Router();
$homeController = new HomeController($smarty);

$router->add('', [$homeController, 'index']);
$router->add('impressum', fn() => $smarty->display('impressum.tpl'));
$router->add('kontakt', fn() => $smarty->display('contact.tpl'));
$router->add('agb', fn() => $smarty->display('terms.tpl'));
$router->add('datenschutz', fn() => $smarty->display('privacy.tpl'));
$router->add('about', function () {
    require __DIR__ . '/about.php';
});
$router->add('404', fn() => header('Location: /studyhub/error/404'));

$path = $_GET['route'] ?? '';

if ($path === '' && isset($_SESSION['user_id']) && ($_SESSION['2fa_passed'] ?? false) === true) {
    header('Location: dashboard');
    exit;
}

$router->dispatch($path);
