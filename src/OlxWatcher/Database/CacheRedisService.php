<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Redis;
use RedisException;

class CacheRedisService implements CacheInterface
{
    /**
     * @throws WatcherException|RedisException
     */
    public function __construct(
        protected Redis $redis,
    )
    {
        $this->redis->connect(Configurator::config()['redis']['host']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
    }

    /**
     * @throws RedisException
     */
    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        return $value === false ? [] : $value;
    }

    /**
     * @throws RedisException
     */
    public function mGet(array $keys): array
    {
        $values = $this->redis->mGet($keys);

        return $values === false ? [] : $values;
    }

    /**
     * @throws RedisException
     */
    public function mSet(array $data): bool
    {
        return $this->redis->mSet($data);
    }

    /**
     * @throws RedisException|WatcherException
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->redis->set($key, $value, Configurator::expiration());
    }

    /**
     * @throws RedisException
     */
    public function keys(string $keyPattern = '*'): array
    {
        $keys = $this->redis->keys($keyPattern);

        return $keys === false ? [] : $keys;
    }
}
