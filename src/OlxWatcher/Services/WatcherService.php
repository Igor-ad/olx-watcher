<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Subjects\SubjectDto;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class WatcherService extends AbstractService
{
    public const EMPTY_LIST = 'No subscriptions yet.';
    public const PRICE_CHANGED = 'The price has changed.';

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
        foreach ($this->cache as $url => $subject) {
            $updatedSubject = $this->comparator($subject, $url, $this->getPrice($url));
            $this->cache->offsetSet($url, $updatedSubject);
        }
    }

    protected function comparator(SubjectDto $subject, string $url, string $price): SubjectDto
    {
        if ($subject->lastPrice !== $price) {
            $this->logger->notice(self::PRICE_CHANGED, [$price, $url]);

            return SubjectDto::updatePrice($subject->toArray(), $price);
        }
        return SubjectDto::changeUpdateFlag($subject->toArray());
    }
}
