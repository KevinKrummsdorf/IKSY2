<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

$code = (int)($_GET['code'] ?? 500);
$allowedCodes = [401, 403, 404, 500, 503];
if (!in_array($code, $allowedCodes, true)) {
    $code = 500;
}
http_response_code($code);

$reason = $_GET['reason'] ?? null;
$action = $_GET['action'] ?? null;

$smarty->assign('code', $code);
$smarty->assign('reason', $reason);
$smarty->assign('action', $action);

$templateFile = "errors/{$code}.tpl";
$fullPath = dirname(__DIR__) . "/templates/{$templateFile}";

if (!file_exists($fullPath)) {
    $templateFile = "errors/500.tpl";
}

$smarty->display($templateFile);
