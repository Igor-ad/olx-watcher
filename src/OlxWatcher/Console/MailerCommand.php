<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

require __DIR__ . '/../../../vendor/autoload.php';

echo date('Y-m-d H:i:s') . Mailer::START . PHP_EOL;
$command = new Mailer();
$result = $command();
echo date('Y-m-d H:i:s') . Mailer::STOP . PHP_EOL;

return $result;
