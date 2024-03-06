<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\WatcherService;

class WatcherCommand
{
    public function __construct(
        protected WatcherService $service,
    )
    {
    }

    public function __invoke(): int
    {
        try {
            $this->service->watch();
        } catch (\Exception $e) {
            echo 'Watcher error: ',  $e->getMessage(), "\n";
        }
    }
}
