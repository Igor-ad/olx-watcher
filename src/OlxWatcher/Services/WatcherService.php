<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheFileService;
use Autodoctor\OlxWatcher\Database\CacheRedisService;
use Autodoctor\OlxWatcher\DTO\SubjectFactory;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class WatcherService extends AbstractService
{
    const EMPTY_LIST = 'No subscriptions yet.';
    const PRICE_CHANGED = 'The price has changed.';

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        if ($this->subjectKeys === []) {
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
        $this->cache->mSet($this->subjectCollect);

        return 0;
    }

    /**
     * @throws WatcherException
     */
    public function subscribeIterator(): void
    {
        foreach ($this->subjectKeys as $key => $url) {
            if ($this->cache instanceof CacheFileService) {
                $this->subjectCollect[$url] = $this->comparator(
                    $this->subjectCollect[$url], $url, $this->getPrice($url)
                );
            }

            if ($this->cache instanceof CacheRedisService) {
                $this->subjectCollect[$key] = $this->comparator(
                    $this->subjectCollect[$key], $url, $this->getPrice($url)
                );
            }
        }
    }

    protected function comparator(array $subject, string $url, string $price): array
    {
        if ($subject['last_price'] !== $price) {
            $this->logger->notice(self::PRICE_CHANGED, [$price, $url]);

            return SubjectFactory::updatePrice($subject, $price)->toArray();
        }
        return SubjectFactory::changeUpdateFlag($subject)->toArray();
    }
}
