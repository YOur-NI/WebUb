<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Service\LoggerService;

$logger = new LoggerService('app.log');
$logger->log('Тестовое сообщение из LoggerService');

echo "Сообщение записано в app.log\n";