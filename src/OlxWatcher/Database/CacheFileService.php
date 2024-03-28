<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\FileProcessor;

class CacheFileService implements CacheInterface
{
    use HelpTrait;

    protected const PATTERN = 'http';

    private array $data;

    /**
     * @throws WatcherException
     */
    public function __construct()
    {
        $this->data = $this->getDataFromFile();
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? [];
    }

    /**
     * @throws WatcherException
     */
    public function getDataFromFile(): array
    {
        return $this->fromString(
            FileProcessor::getContent(FilesEnum::SUBSCRIBE_FILE)
        ) ?? [];
    }

    public function mGet(array $keys): array
    {
        return array_filter($this->data, fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @throws WatcherException
     */
    public function mSet(array $data): bool
    {
        return (bool)FileProcessor::putContent(
            FilesEnum::SUBSCRIBE_FILE,
            $this->toString(array_merge($this->getDataFromFile(), $data)),
            LOCK_EX
        );
    }

    /**
     * @throws WatcherException
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->mSet([$key => $value]);
    }

    public function keys(string $keyPattern = ''): array
    {
        return array_filter(
            array_keys($this->data), fn($key) => str_starts_with($key, self::PATTERN)
        );
    }
}
