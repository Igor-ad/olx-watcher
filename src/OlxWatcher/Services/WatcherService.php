<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Subjects\SubjectFactory;
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

        return 0;
    }

    /**
     * @throws WatcherException
     */
    public function subscribeIterator(): void
    {
        foreach ($this->cache as $url => $value) {
            $subjectHash = $this->comparator($value, $url, $this->getPrice($url));
            $this->cache->offsetSet($url, $subjectHash);
        }
    }

    protected function comparator(array $subjectHash, string $url, string $price): array
    {
        if ($subjectHash['last_price'] !== $price) {
            $this->logger->notice(self::PRICE_CHANGED, [$price, $url]);

            return SubjectFactory::updatePrice($subjectHash, $price)->toArray();
        }
        return SubjectFactory::changeUpdateFlag($subjectHash)->toArray();
    }
}
