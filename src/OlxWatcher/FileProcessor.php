<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Enums\AccessLevelEnum;

class FileProcessor
{
    /**
     * @throws WatcherException
     */
    public static function getContent(string $fileName): string
    {
        $data = file_get_contents($fileName);

        if ($data) {
            return $data;
        }
        throw new WatcherException(sprintf('There is no data in the %s file.', $fileName));
    }

    /**
     * @throws WatcherException
     */
    protected static function read(string $fileName, string $level): string
    {
        $fp = fopen($fileName, $level);
        if (flock($fp, LOCK_SH | LOCK_NB)) {
            $data = self::getContent($fileName);
            flock($fp, LOCK_UN);
            fclose($fp);
            return $data;
        }
            throw new WatcherException(sprintf('Failed to obtain lock on file %s', $fileName));
    }

    /**
     * @throws WatcherException
     */
    public static function reader(string $fileName, string $accessLevel): string
    {
        if (file_exists($fileName)) {
            return self::read($fileName, $accessLevel);
        }
        throw new WatcherException(sprintf('%s file not found', $fileName));
    }

    /**
     * @throws WatcherException
     */
    public static function readOnly(string $fileName): string
    {
        return self::reader($fileName, AccessLevelEnum::READ_ONLY);
    }

    public static function simpleDelete(string $fileName): void
    {
        unlink($fileName);
    }

    /**
     * @throws WatcherException
     */
    protected static function write(string $fileName, string $accessLevel, string $data): int
    {
        $fp = fopen($fileName, $accessLevel);
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $data = fwrite($fp, $data);
            if (!$data) {
                throw new WatcherException(sprintf('The file %s is not writable.', $fileName));
            }
            flock($fp, LOCK_UN);
            fclose($fp);
            return $data;
        }
        throw new WatcherException(sprintf('The %s file is not blocked.', $fileName));
    }

    /**
     * @throws WatcherException
     */
    public static function writeNewFile(string $fileName, string $data): int
    {
        return self::write($fileName, AccessLevelEnum::NEW_WRITE, $data);
    }

    /**
     * @throws WatcherException
     */
    public static function writeToTheEnd(string $fileName, string $data): int
    {
        return self::write($fileName, AccessLevelEnum::WRITE_END, $data);
    }
}
