<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function mGet(array $keys): array;

    public function keys(string $keyPattern): array;

    public function set(string $key, mixed $value): bool;

    public function mSet(array $data): bool;
}
