<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Mail\Mailer;

require __DIR__ . '/../../../vendor/autoload.php';

try {
    echo 'Mailer started at ' . date('Y-m-d H:i:s') . PHP_EOL;
    $cacheDriver = CacheFactory::getCacheDriver();
    $mailer = new Mailer($cacheDriver);
    $mailer();
    echo 'Mailer stopped at ' . date('Y-m-d H:i:s') . PHP_EOL;
} catch (\Exception $e) {
    echo 'Mail error: ' . $e->getMessage() . PHP_EOL;
}