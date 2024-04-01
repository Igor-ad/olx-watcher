<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Logger;

trait LogFormatter
{
    protected function toString($level, string $message, array $context): string
    {
        $pid = $this->pidToString(getmypid());

        return sprintf(
            '%s | pid: %s | %s: %s %s %s',
            date('Y-m-d H:i:s'), $pid, $level,
            $message, $this->contextToString($context), PHP_EOL
        );
    }

    protected function contextToString(array $context): string
    {
        return implode(' | ', $context);
    }

    protected function pidToString($pid): string
    {
        return match (true) {
            $pid < 100 => $pid . '   ',
            $pid < 1000 => $pid . '  ',
            $pid < 10000 => $pid . ' ',
            default => $pid,
        };
    }

    public function getExceptionLogContext(\Exception $error): array
    {
        return [
            $error->getMessage(),
            $error->getCode(),
            PHP_EOL . $error->getTraceAsString(),
        ];
    }
}