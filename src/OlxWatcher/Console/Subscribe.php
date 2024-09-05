<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Controllers\SubscribeController;
use Autodoctor\OlxWatcher\Services\SubscribeService;

class Subscribe extends AbstractCommand
{
    const ERROR = 'Subscribe error. ';

    public function __invoke(): int|string
    {
        return $this->handler(SubscribeService::class);
    }

    protected function commandClosure(string $serviceName, $logger): \Closure
    {
        return function () use ($logger, $serviceName) {
            $service = new $serviceName();
            $service->setLogger($logger);
            $controller = new SubscribeController($service);

            return $controller();
        };
    }
}
