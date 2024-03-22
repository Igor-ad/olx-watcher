<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Logger\Logger;
use Autodoctor\OlxWatcher\Mail\Mailer;

require __DIR__ . '/../../../vendor/autoload.php';

const START = 'Mailer started ';
const STOP = 'Mailer stopped ';
const ERROR = 'Mailer error. ';

$logger = new Logger();

try {
    echo $logger->cronToString(START);
    $logger->info(START);

    $cacheDriver = CacheFactory::getCacheDriver();
    $mailer = new Mailer($cacheDriver);
    $mailer->setLogger($logger);
    $mailer();

    echo $logger->cronToString(STOP);
    $logger->info(STOP);
} catch (\Exception $e) {
    echo ERROR . $e->getMessage() . PHP_EOL;
    $logger->error(ERROR, [$e->getMessage(), $e->getCode(), PHP_EOL . $e->getTraceAsString()]);
}