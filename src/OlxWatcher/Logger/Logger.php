<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Logger;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\FileProcessor;
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    use LogFormatter;

    protected string $logFile;

    public function __construct()
    {
        $this->logFile = FilesEnum::LOG_FILE;
    }

    /**
     * @throws WatcherException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        FileProcessor::putContent(
            $this->logFile, $this->toString($level, $message, $context), FILE_APPEND);
    }
}