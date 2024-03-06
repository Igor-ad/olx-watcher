<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

interface CacheInterface
{
    public static function get(string $key): mixed;

    public static function set(string $key, mixed $data): void;
}
