<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

class ParserFactory
{
    public static function getParser(): Parser
    {
        return new Parser();
    }
}
