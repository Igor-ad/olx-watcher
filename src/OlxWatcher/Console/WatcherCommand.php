<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

require __DIR__ . '/../../../vendor/autoload.php';

$command = new Watcher();

return $command();
