<?php

use Autodoctor\OlxWatcher\CacheFileService;
use Autodoctor\OlxWatcher\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

try {
    $cacheDriver = new CacheFileService;
    $subscribe = new SubscribeService($cacheDriver);

    if ($subscribe->subscribe() === 0) {
        echo 'Ok!';
    } else {
        echo 'Something went wrong';
    }
} catch (Exception $e) {
    echo 'Subscribe error: ',  $e->getMessage(), "\n";
}


