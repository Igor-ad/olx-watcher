<?php

use Autodoctor\OlxWatcher\Controllers\SubscribeController;
use Autodoctor\OlxWatcher\Logger\Logger;
use Autodoctor\OlxWatcher\Services\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

const OK = 'SubscribeService. OK!';
const WRONG = 'Subscribe. Something went wrong.';
const ERROR = 'Subscribe error. ';

$logger = new Logger();

try {
    $service = new SubscribeService();
    $service->setLogger($logger);
    $controller = new SubscribeController($service);
    $result = $controller();

    if ($result === 0) {
        echo OK;
        $logger->info(OK);
    } else {
        echo WRONG;
        $logger->warning(WRONG);
    }
} catch (Exception $e) {
    echo ERROR . $e->getMessage();
    $logger->error(ERROR, $logger->getExceptionLogContext($e));
}


