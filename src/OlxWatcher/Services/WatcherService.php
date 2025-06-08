<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Database\FileRepository;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Notifiers\NotifierFactory;
use Autodoctor\OlxWatcher\Subjects\AdsDTO;

class WatcherService extends AbstractService
{
    public const EMPTY_LIST = 'No subscriptions yet.';
    public const PRICE_CHANGED = 'The price has changed.';
    public const NOTIFY = 'Subscribers have just been notified.';

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        if ($this->subjectKeys === []) {
            $this->logger->notice(self::EMPTY_LIST);

            return 0;
        }
        $this->iterate();

        return 0;
    }

    /**
     * @throws WatcherException|\Exception
     */
    protected function iterate(): void
    {
        foreach ($this->cache as $url => $subject) {
            if (is_a($this->cache, FileRepository::class)) {
                if ($this->cache->isExpired($subject->lastTime, Configurator::expiration())) {
                    $this->cache->remove($url);

                    continue;
                }
            }
            $updatedSubject = $this->comparison($subject, $url, $this->getPrice($url));
            $this->cache->offsetSet($url, $updatedSubject);
        }
    }

    /**
     * @throws WatcherException
     */
    protected function comparison(AdsDTO $adsDTO, string $url, string $price): AdsDTO
    {
        if ($adsDTO->lastPrice !== $price) {
            $this->logger->notice(self::PRICE_CHANGED, [$price, $url]);
            $updatedAdsDTO = AdsDTO::updatePrice($adsDTO->toArray(), $price);
            $this->notify($updatedAdsDTO);
            $this->logger->notice(self::NOTIFY);

            return $updatedAdsDTO;
        }
        return AdsDTO::changeUpdateFlag($adsDTO->toArray());
    }

    /**
     * @throws WatcherException
     */
    private function notify(AdsDTO $adsDTO): void
    {
        $this->subject->setDTO($adsDTO);
        $this->subject->attach(NotifierFactory::getNotifier());
        $this->subject->notify();
    }
}
