<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class Parser
{
    use CurlTrait;

    const STRING_START = '<title data-rh="true">';
    const STRING_END = '</title>';
    const CURRENCY_START = ': ';
    const CURRENCY_STRING = 'грн';
    const ERROR = 'Хьюстон, в нас проблема.';
    const TIME_OUT = 15;
    const CONNECTTIMEOUT = 20;

    protected string $targetUrl;
    protected string $target = '';
    protected string $title = '';
    protected string $price = '';

    public function parse(): void
    {
        $this->target = $this->getUri($this->targetUrl);
        $this->checkUrl($this->target);
        $this->parseTitle();
        $this->parsePrice();
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function checkUrl(string $target): bool
    {
        if (stripos($target, self::STRING_END) === false) {
            throw new WatcherException('Target URL not available.');
        }
        return true;
    }

    protected function cutter(string $input, string $start, string $end): string
    {
        $temp = stristr($input, $end, true);

        return str_replace(
            $start, '',
            strstr($temp, $start),
        );
    }

    public function parsePrice(): void
    {
        $tempPrice = trim($this->cutter(
            input: $this->title,
            start: self::CURRENCY_START,
            end: self::CURRENCY_STRING,
        ));
        $this->price = str_replace(' ', '', $tempPrice);
    }

    public function parseTitle(): void
    {
        $this->title = $this->cutter(
            input: $this->target,
            start: self::STRING_START,
            end: self::STRING_END,
        );
    }
}
