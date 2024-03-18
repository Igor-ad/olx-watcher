<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class Configurator
{
    /**
     * @throws WatcherException
     */
    public static function config(): array|false
    {
        $config = parse_ini_file(FilesEnum::CONFIG_FILE, true);

        if ($config === false) {
            throw new WatcherException('Error reading configuration.');
        }
    }
}
