<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class CacheRedisService implements CacheInterface
{
    protected array $config;

    /**
     * @throws WatcherException|\RedisException
     */
    public function __construct(
        protected \Redis $redis,
    )
    {
        $this->config = Configurator::config();
        $this->redis->connect($this->config['redis']['host']);
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): mixed
    {
            $data = $this->redis->get($key);

            if ($data === false) {
                return [];
            }
            return json_decode($data, true);
    }

    /**
     * @throws \RedisException
     */
    public function set(string $key, mixed $data): void
    {
        $this->redis->set($key, json_encode($data));
    }
}
