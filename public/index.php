<?php

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Services\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

try {
    $cacheDriver = CacheFactory::getCacheDriver();
    $subscribe = new SubscribeService($cacheDriver);

    if ($subscribe->subscribe() === 0) {
        echo 'Ok!';
    } else {
        echo 'Something went wrong';
    }
} catch (Exception $e) {
    echo 'Subscribe error: ' . $e->getMessage() . PHP_EOL;
}


