<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Mail\Mailer;

class MailerCommand
{
    public function __construct(
        protected Mailer $mailer,
    )
    {
    }

    public function __invoke(): int
    {
        try {
            $this->mailer->sender();
        } catch (\Exception $e) {
            echo 'Mail error: ',  $e->getMessage(), "\n";
        }
    }
}