<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

trait Formatter
{
    public const RN = "\r\n";
    public const UNSUBSCRIBE = 'Click the following URL to unsubscribe from this service: ';

    protected function formatMessage(string $url, string $email): string
    {
        return $this->formatPriceUpdateMessage($url) . self::RN
            . self::UNSUBSCRIBE . self::RN
            . $this->formatUnsubscribeUrl($url, $email);
    }

    protected function formatPriceUpdateMessage(string $url): string
    {
        return sprintf(
            '%s: %s %s %s New price: %s at %s %s Previous price: %s at %s.',
            $this->config['mail']['message'], self::RN,
            $url, self::RN,
            $this->subscribe['last_price'], $this->subscribe['last_time'], self::RN,
            $this->subscribe['previous_price'], $this->subscribe['previous_time']
        );
    }

    protected function formatUnsubscribeUrl(string $url, string $email): string
    {
        return sprintf(
            '%s?status=unsubscribe&email=%s&url=%s',
            $this->config['metadata']['app_url'], $email, $url
        );
    }
}
