<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

trait Formatter
{
    public const RN = "\r\n";

    protected function priceUpdateMessage(string $url): string
    {
        return sprintf(
            '%s: %s %s %s New price: %s at %s %s Previous price: %s at %s.',
            $this->config['mail']['message'], self::RN,
            $url, self::RN,
            $this->subscribe[$url]['last_price'], $this->subscribe[$url]['last_time'], self::RN,
            $this->subscribe[$url]['previous_price'], $this->subscribe[$url]['previous_time']
        );
    }
}
