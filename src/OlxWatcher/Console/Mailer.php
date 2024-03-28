<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Mail\Mailer as MailService;

class Mailer extends AbstractCommand
{
    public const START = 'Mailer started.';
    public const STOP = 'Mailer stopped.';
    protected const ERROR = 'Mailer error. ';

    public function __invoke(): int|string
    {
        return $this->handler(MailService::class);
    }
}
