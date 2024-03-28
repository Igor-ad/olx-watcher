<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheFileService;
use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Database\CacheRedisService;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Parser;
use Autodoctor\OlxWatcher\ParserFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WatcherService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const EMPTY_LIST = 'No subscriptions yet.';

    protected array $subscribe = [];
    protected array $subscribeKeys = [];
    protected array $updatedKeys = [];
    protected Parser $parser;

    public function __construct(
        protected CacheInterface $cache,
    )
    {
        $this->subscribeKeys = $this->cache->keys('http*');
        $this->subscribe = $this->cache->mGet($this->subscribeKeys);
        $this->parser = ParserFactory::getParser();
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

    /**
     * @throws WatcherException
     */
    public function watch(): int
    {
        $this->subscribeIterator();

        $this->cache->set('updated', $this->updatedKeys);
        $this->cache->mSet($this->subscribe);

        return 0;
    }

    /**
     * @throws WatcherException
     */
    public function subscribeIterator(): void
    {
        foreach ($this->subscribeKeys as $key => $url) {
            if ($this->cache instanceof CacheFileService) {
                $this->subscribe[$url] = $this->comparator(
                    $this->subscribe[$url], $url, $this->priceParser($url)
                );
            }

            if ($this->cache instanceof CacheRedisService) {
                $this->subscribe[$key] = $this->comparator(
                    $this->subscribe[$key], $url, $this->priceParser($url)
                );
            }
        }
    }

    /**
     * @throws WatcherException
     */
    public function priceParser(string $url): string
    {
        $this->parser->setTargetUrl($url);
        $this->parser->parse();
        return $this->parser->getPrice();
    }

    protected function comparator(array $subscribeItem, string $url, string $price): array
    {
        if ($subscribeItem['last_price'] !== $price) {
            $this->updatedKeys[] = $url;
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
