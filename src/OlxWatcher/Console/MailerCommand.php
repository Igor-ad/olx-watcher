<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Mail\Mailer;

require __DIR__ . '/../../../vendor/autoload.php';

try {
    $mailer = new Mailer();
    $mailer();
} catch (\Exception $e) {
    echo 'Mail error: ',  $e->getMessage(), "\n";
}