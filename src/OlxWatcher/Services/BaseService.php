<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\ParserFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class BaseService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?CacheInterface $cache = null;
    protected array $subscribe;

    /**
     * @throws \RedisException|WatcherException
     */
    public function __construct(
    )
    {
        $this->setCache();
    }

    /**
     * @throws \RedisException|WatcherException
     */
    public function setCache(): void
    {
        $this->cache = CacheFactory::getCacheDriver();
    }

    public function setSubscribe(string $url): void
    {
        $this->subscribe = $this->cache->get($url) ?? [];
    }

    /**
     * @throws WatcherException
     */
    protected function getPrice(string $url): string
    {
        $parser = ParserFactory::getParser();
        $parser->setTargetUrl($url);
        $parser->parse();

        return $parser->getPrice();
    }
}