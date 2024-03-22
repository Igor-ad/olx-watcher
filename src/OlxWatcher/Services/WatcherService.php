<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\ParserFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WatcherService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const NOTICE = 'No subscriptions yet.';

    protected array $subscribe = [];
    protected array $updatedKeys = [];

    public function __construct(
        protected CacheInterface $cache,
    )
    {
        $this->subscribe = $this->cache->get('subscribe');
    }

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        if ($this->subscribe === []) {
            echo self::NOTICE . PHP_EOL; // insert into /var/log/cron.log
            $this->logger->notice(self::NOTICE);

            return 0;
        }
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

        $this->cache->set('subscribe', $this->subscribe);
        $this->cache->set('updated', $this->updatedKeys);

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
