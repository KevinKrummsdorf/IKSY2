<?php

use Monolog\Logger;

class MonologLoggerAdapter implements ILogger {
    private Logger $logger;
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function debug(string $message, array $context = []): void {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = []): void {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = []): void {
        $this->logger->critical($message, $context);
    }
}
