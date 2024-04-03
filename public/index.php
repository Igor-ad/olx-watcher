<?php

use Autodoctor\OlxWatcher\Controllers\SubscribeController;
use Autodoctor\OlxWatcher\Logger\Logger;
use Autodoctor\OlxWatcher\Services\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

const ERROR = 'Subscribe error. ';

$logger = new Logger();

try {
    $service = new SubscribeService();
    $service->setLogger($logger);
    $controller = new SubscribeController($service);
    echo $controller();
} catch (Exception $e) {
    $logger->error(ERROR, $logger->getExceptionLogContext($e));
    echo ERROR;
}


