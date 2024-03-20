<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Services\WatcherService;

require __DIR__ . '/../../../vendor/autoload.php';

try {
    echo 'Watcher started at ' . date('Y-m-d H:i:s') . PHP_EOL;
    $cacheDriver = CacheFactory::getCacheDriver();
    $watcher = new WatcherService($cacheDriver);
    $watcher();
    echo 'Watcher stopped at ' . date('Y-m-d H:i:s') . PHP_EOL;
} catch (\Exception $e) {
    echo 'Watcher error: ' . $e->getMessage() . PHP_EOL;
}
