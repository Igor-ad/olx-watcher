<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

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
            'file' => new CacheFileService(),
        };
    }

    private static function typeCacheDriver(): string
    {
        return Configurator::config()['cache']['type'] ?? 'file';
    }
}