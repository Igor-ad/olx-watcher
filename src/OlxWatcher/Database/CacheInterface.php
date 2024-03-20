<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $data): void;
}
