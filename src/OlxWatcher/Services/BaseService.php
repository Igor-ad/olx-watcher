<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\RepositoryFactory;
use Autodoctor\OlxWatcher\Database\Cache;
use Autodoctor\OlxWatcher\Subjects\SubjectDto;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Parsers\ParserFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RedisException;

class BaseService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Cache $cache;
    protected SubjectDto|false $subject;

    /**
     * @throws RedisException|WatcherException
     */
    public function __construct()
    {
        $this->setCache();
    }

    /**
     * @throws RedisException|WatcherException
     */
    public function setCache(): void
    {
        $this->cache = RepositoryFactory::getCacheDriver();
    }

    public function setSubject(string $url): void
    {
        $data = $this->cache->offsetGet($url);
        $this->subject = is_a($data, SubjectDto::class) ? $data : false;
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
