<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheFileService;
use Autodoctor\OlxWatcher\Database\CacheRedisService;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class WatcherService extends BaseService
{
    const EMPTY_LIST = 'No subscriptions yet.';
    const PRICE_CHANGED = 'The price has changed.';

    protected array $subscribeCollect = [];
    protected array $subscribeKeys = [];
    protected array $updatedKeys = [];

    public function __construct()
    {
        parent::__construct();
        $this->subscribeKeys = $this->cache->keys('http*');
        $this->setSubscribeCollect($this->subscribeKeys);
    }

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        if ($this->subscribeKeys === []) {
            $this->logger->notice(self::EMPTY_LIST);

            return 0;
        }
        return $this->watch();
    }

    protected function setSubscribeCollect(array $keys): void
    {
        $this->subscribeCollect = $this->cache->mGet($keys);
    }

    /**
     * @throws WatcherException
     */
    public function watch(): int
    {
        $this->subscribeIterator();

        $this->cache->set('updated', $this->updatedKeys);
        $this->cache->mSet($this->subscribeCollect);

        return 0;
    }

    /**
     * @throws WatcherException
     */
    public function subscribeIterator(): void
    {
        foreach ($this->subscribeKeys as $key => $url) {
            if ($this->cache instanceof CacheFileService) {
                $this->subscribeCollect[$url] = $this->comparator(
                    $this->subscribeCollect[$url], $url, $this->getPrice($url)
                );
            }

            if ($this->cache instanceof CacheRedisService) {
                $this->subscribeCollect[$key] = $this->comparator(
                    $this->subscribeCollect[$key], $url, $this->getPrice($url)
                );
            }
        }
    }

    protected function comparator(array $subscribeItem, string $url, string $price): array
    {
        if ($subscribeItem['last_price'] !== $price) {
            $this->updatedKeys[] = $url;
            $this->logger->notice(self::PRICE_CHANGED, [$price, $url]);

            return $this->subscribeResource($subscribeItem, $price);
        }
        return $subscribeItem;
    }

    protected function subscribeResource(array $subscribeItem, string $price): array
    {
        return [
            'previous_price' => $subscribeItem['last_price'],
            'last_price' => $price,
            'previous_time' => $subscribeItem['last_time'],
            'last_time' => date("Y-m-d H:i:s"),
            'subscribers' => $subscribeItem['subscribers']
        ];
    }
}
