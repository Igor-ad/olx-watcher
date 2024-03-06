<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Enums;

enum AccessLevelEnum: string
{
    const WRITE_END = 'a';
    const READ_WRITE_END = 'a+';
    const NEW_OR_WRITE = 'c';
    const NEW_OR_READ_WRITE = 'c+';
    const CLOSE_ON_EXEC = 'e';
    const READ_ONLY = 'r';
    const READ_WRITE = 'r+';
    const NEW_WRITE = 'w';
    const NEW_READ_WRITE = 'w+';
    const WRITE_ONLY_START = 'x';
    const READ_WRITE_START = 'x+';
}
