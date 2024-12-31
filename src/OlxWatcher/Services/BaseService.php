<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheFactory;
use Autodoctor\OlxWatcher\Database\Cache;
use Autodoctor\OlxWatcher\DTO\SubjectDto;
use Autodoctor\OlxWatcher\DTO\SubjectFactory;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\ParserFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class BaseService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Cache $cache;
    protected SubjectDto|bool $subject;

    /**
     * @throws \RedisException|WatcherException
     */
    public function __construct()
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

    public function setSubject(string $url): void
    {
        $data = $this->cache->get($url);
        $this->subject = $data ? SubjectFactory::createFromArray($data) : false;
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
