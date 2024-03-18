<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Enums;

enum FilesEnum: string
{
    const CONFIG_FILE = __DIR__ . '/../../config.ini';
    const LOG_FILE = __DIR__ . '/../../olx_watcher.log';
    const SUBSCRIBE_FILE = __DIR__ . '/../../subscribe.json';
}
