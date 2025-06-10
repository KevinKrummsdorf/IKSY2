<?php

require_once __DIR__ . '/DummyLogger.php';
require_once __DIR__ . '/MonologLoggerAdapter.php';
require_once __DIR__ . '/ILogger.php';
require_once __DIR__ . '/../includes/config.inc.php';

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

class LoggerFactory {
    
    public static function get(string $channel): ILogger {
    global $config;

    if (empty($config['log']['debug'])) {
        return new DummyLogger();
    }

    $logger = new Logger($channel);
    $date = date('Y-m-d');
    $logFile = __DIR__ . "/../logs/{$channel}_{$date}.log";
    $handler = new StreamHandler($logFile, Level::Debug);
    $logger->pushHandler($handler);

    // Alte Logdateien löschen
    $logDays = $config['log']['log_days'] ?? 30;
    $logPattern = __DIR__ . "/../logs/{$channel}_*.log";

    foreach (glob($logPattern) as $file) {
        if (filemtime($file) < time() - ($logDays * 86400)) {
            @unlink($file);
        }
    }

    return new MonologLoggerAdapter($logger);
}

}

