<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class WatcherService
{
    protected array $subscribe = [];
    protected array $updatedKeys = [];

    /**
     * @throws WatcherException
     */
    public function __construct()
    {
        $this->subscribe = CacheFileService::get(FilesEnum::SUBSCRIBE_FILE);
    }

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        return $this->watch();
    }

    public function getSubscribeUrlList(): array
    {
        return array_keys($this->subscribe);
    }

    /**
     * @throws WatcherException
     */
    public function watch(): int
    {
        $this->parseNewPriceList();

        CacheFileService::set(FilesEnum::SUBSCRIBE_FILE, $this->subscribe);
        CacheFileService::set(FilesEnum::UPDATE_KEYS_FILE, $this->updatedKeys);

        return 0;
    }

    /**
     * @throws WatcherException
     */
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
