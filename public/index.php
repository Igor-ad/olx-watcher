<?php

use Autodoctor\OlxWatcher\SubscribeService;

require __DIR__ . '/../vendor/autoload.php';

try {
    $subscribe = new SubscribeService();

    if ($subscribe->subscribe() === 0) {
        echo 'Ok!';
    } else {
        echo 'Something went wrong';
    }
} catch (Exception $e) {
    echo 'Subscribe error: ',  $e->getMessage(), "\n";
}


