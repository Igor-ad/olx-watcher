<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Enums\FilesEnum;

class WatcherService
{
    protected array $subscribe = [];
    protected array $updatedKeys = [];

    public function __construct()
    {
        $this->subscribe = CacheFileService::get(FilesEnum::SUBSCRIBE_FILE);
    }

    public function getSubscribeUrlList(): array
    {
        return array_keys($this->subscribe);
    }

    public function watch(): int
    {
        $this->parseNewPriceList();

        CacheFileService::set(FilesEnum::SUBSCRIBE_FILE, $this->subscribe);
        CacheFileService::set(FilesEnum::UPDATE_KEYS_FILE, $this->updatedKeys);

        return 0;
    }

    public function parseNewPriceList(): void
    {
        foreach ($this->getSubscribeUrlList() as $url) {
            $parser = ParserFactory::getParser();
            $parser->setTargetUrl($url);
            $parser->parse();
            $price = $parser->getPrice();
            $this->comparator($url, $price);
        }
    }

    protected function comparator(string $url, string $price): array
    {
        if ($this->subscribe[$url]['last_price'] != $price) {
            $this->updatedKeys[] = $url;
            $this->updateSubscribePrice($url, $price);
        }
        return $this->subscribe[$url];
    }

    protected function updateSubscribePrice(string $url, string $price): void
    {
        $this->subscribe[$url]['previous_price'] = $this->subscribe[$url]['last_price'];
        $this->subscribe[$url]['last_price'] = $price;
        $this->subscribe[$url]['previous_time'] = $this->subscribe[$url]['last_time'];
        $this->subscribe[$url]['last_time'] = date("Y-m-d H:m:i");
    }
}
