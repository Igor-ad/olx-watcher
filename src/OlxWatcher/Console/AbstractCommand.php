<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Logger\Logger;

abstract class AbstractCommand
{
    abstract public function __invoke(): int|string;

    public function handler(string $serviceName): int|string
    {
        $logger = new Logger();

        try {
            $logger->info(static::START);
            $service = new $serviceName();
            $service->setLogger($logger);
            $result = $service();
            $logger->info(static::STOP);

            return $result;
        } catch (\Exception $e) {
            $logger->error(static::ERROR, $logger->getExceptionLogContext($e));

            return static::ERROR . $e->getMessage();
        }
    }
}
