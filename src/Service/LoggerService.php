<?php
namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerService
{
    private Logger $logger;

    public function __construct(string $logFile = 'app.log')
    {
        $this->logger = new Logger('app');
        $this->logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
    }

    public function log(string $message): void
    {
        $this->logger->info($message);
    }
}