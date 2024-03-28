<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Services\WatcherService;

class Watcher extends AbstractCommand
{
    protected const START = 'Watcher started.';
    protected const STOP = 'Watcher stopped.';
    protected const ERROR = 'Watcher error. ';

    public function __invoke(): int|string
    {
        return $this->handler(WatcherService::class);
    }
}
