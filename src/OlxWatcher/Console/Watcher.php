<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Services\WatcherService;

class Watcher extends AbstractCommand
{
    public const START = 'Watcher started.';
    public const STOP = 'Watcher stopped.';
    protected const ERROR = 'Watcher error. ';

    public function __invoke(): int|string
    {
        return $this->handler(WatcherService::class);
    }
}
