<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class CacheFileService implements CacheInterface
{
    /**
     * @throws WatcherException
     */
    public static function get(string $key): mixed
    {
        return json_decode(FileProcessor::getContent($key), true);
    }

    /**
     * @throws WatcherException
     */
    public static function set(string $key, mixed $data): void
    {
        FileProcessor::putContent($key, json_encode($data));
    }
}
