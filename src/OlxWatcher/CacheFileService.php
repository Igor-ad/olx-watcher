<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class CacheFileService implements CacheInterface
{
    /**
     * @throws WatcherException
     */
    public function get(string $key): mixed
    {
        return $this->getData()[$key];
    }

    /**
     * @throws WatcherException
     */
    public function set(string $key, mixed $data): void
    {
        FileProcessor::putContent(
            FilesEnum::SUBSCRIBE_FILE, json_encode(array_merge($this->getData(), [$key => $data]))
        );
    }

    /**
     * @throws WatcherException
     */
    public function getData(): array
    {
        return json_decode(
            FileProcessor::getContent(FilesEnum::SUBSCRIBE_FILE), true
        ) ?? [];
    }
}
