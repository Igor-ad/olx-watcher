<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\WatcherService;

require __DIR__ . '/../../../vendor/autoload.php';

try {
    $watcher = new WatcherService();
    $watcher();
} catch (\Exception $e) {
    echo 'Watcher error: ',  $e->getMessage(), "\n";
}
