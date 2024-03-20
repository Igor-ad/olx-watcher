<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class CacheFactory
{
    /**
     * @throws WatcherException|\RedisException
     */
    public static function getCacheDriver(): CacheInterface
    {
        $cacheDriver = self::typeCacheDriver();

        return match ($cacheDriver) {
            'redis' => new CacheRedisService(new \Redis()),
            default => new CacheFileService(),
        };
    }

    /**
     * @throws WatcherException
     */
    private static function typeCacheDriver(): string
    {
        return empty(Configurator::config()['cache']['type'])
            ? 'file'
            : Configurator::config()['cache']['type'];
    }
}