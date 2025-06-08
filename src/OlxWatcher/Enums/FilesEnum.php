<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Enums;

enum FilesEnum: string
{
    case ConfigFile = '/config.ini';
    case LogFile = '/olx_watcher.log';
    case SubscribeFile = '/subscribe.db';

    public function getPath(): string
    {
        $basePath = dirname(__DIR__, 2);

        return $basePath . DIRECTORY_SEPARATOR . ltrim($this->value, '/');
    }
}
