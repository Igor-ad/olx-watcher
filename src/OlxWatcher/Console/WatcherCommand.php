<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Logger\Logger;
use Autodoctor\OlxWatcher\Services\WatcherService;

require __DIR__ . '/../../../vendor/autoload.php';

const START = 'Watcher started ';
const STOP = 'Watcher stopped ';
const ERROR = 'Watcher error: ';

$logger = new Logger();

try {
    echo $logger->cronToString(START);
    $logger->info(START);

    $cacheDriver = CacheFactory::getCacheDriver();
    $watcher = new WatcherService($cacheDriver);
    $watcher->setLogger($logger);
    $watcher();

    echo $logger->cronToString(STOP);
    $logger->info(STOP);
} catch (\Exception $e) {

    echo $logger->cronToString(ERROR . $e->getMessage());
    $logger->error(ERROR, [$e->getMessage(), $e->getCode(), PHP_EOL . $e->getTraceAsString()]);
}
