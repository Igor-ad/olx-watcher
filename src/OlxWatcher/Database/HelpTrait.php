<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

trait HelpTrait
{
    protected function fromString(string $value): mixed
    {
        return json_decode($value, true);
    }

    protected function toString(mixed $value): string
    {
        return json_encode($value);
    }
}
