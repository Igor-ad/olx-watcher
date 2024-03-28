<?php

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Logger\Logger;
use Autodoctor\OlxWatcher\Services\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

const OK = 'SubscribeService. OK!';
const WRONG = 'Subscribe. Something went wrong';
const ERROR = 'Subscribe error. ';

$logger = new Logger();

try {
    $cacheDriver = CacheFactory::getCacheDriver();
    $subscribe = new SubscribeService($cacheDriver);
    $subscribe->setLogger($logger);

    if ($subscribe->subscribe() === 0) {
        echo OK;
        $logger->info(OK);
    } else {
        echo WRONG;
        $logger->warning(WRONG);
    }
} catch (Exception $e) {
    echo ERROR . $e->getMessage();
    $logger->error(ERROR, [$e->getMessage(), $e->getCode(), PHP_EOL . $e->getTraceAsString()]);
}


