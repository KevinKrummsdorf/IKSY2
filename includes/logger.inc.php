<?php
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

define('LOG_DIR', __DIR__ . '/../logs');
global $config;
$debug   = $config['log']['debug'] ?? false;
$logDays = $config['log']['log_days'] ?? 30;

function getLogger(string $channel): Logger {
    static $loggers = [];
    global $debug, $logDays;

    if (!isset($loggers[$channel])) {
        $logger = new Logger($channel);
        $file = LOG_DIR . '/' . $channel . '.log';
        $level = $debug ? Logger::DEBUG : Logger::WARNING;

        $handler = new RotatingFileHandler($file, $logDays, $level);
        $formatter = new LineFormatter("[%datetime%] %level_name%: %message% %context%\n", null, true, true);
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);
        $loggers[$channel] = $logger;
    }

    return $loggers[$channel];
}

function setupGlobalErrorHandling(): void {
    $errorLogger = getLogger('error');

    set_error_handler(function ($severity, $message, $file, $line) use ($errorLogger) {
        $errorLogger->error("PHP Error [$severity] in $file at line $line: $message");
    });

    set_exception_handler(function ($e) use ($errorLogger) {
        $errorLogger->critical("Uncaught Exception: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    });
}

setupGlobalErrorHandling();
