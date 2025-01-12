<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Subjects\SubjectDto;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class SubscribeService extends BaseService
{
    public const NEW_SUBSCRIBE = 'Subscription to the resource has been completed.';
    public const SUBSCRIBE = 'A subscription to this resource has already been completed.';
    public const UNSUBSCRIBE = 'Unsubscribe from the resource is complete.';

    /**
     * @throws WatcherException
     */
    public function subscribe(string $url, string $email): string
    {
        if ($this->subject === false) {
            $price = $this->getPrice($url);
            $this->subject = SubjectDto::createFromRequest($price, $email);
            $this->logger->notice(self::NEW_SUBSCRIBE, [$email, $url]);
            $message = self::NEW_SUBSCRIBE;
        } else {
            $message = $this->addNewSubscriber($url, $email);
        }
        $this->cache->offsetSet($url, $this->subject);

        return $message;
    }

    protected function addNewSubscriber(string $url, string $email): string
    {
        if (in_array($email, $this->subject->subscribers)) {
            $this->logger->notice(self::SUBSCRIBE, [$email, $url]);

            return self::SUBSCRIBE;
        } else {
            $this->subject = SubjectDto::addSubscribers($this->subject->toArray(), $email);
            $this->logger->notice(self::NEW_SUBSCRIBE, [$email, $url]);

            return self::NEW_SUBSCRIBE;
        }
    }

    public function unsubscribe(string $url, string $email): string
    {
        if ($this->subject) {
            $this->unsubscribeFromMailingList($email);
            $this->cache->offsetSet($url, $this->subject);
        }
        $this->logger->notice(self::UNSUBSCRIBE, [$email, $url]);

        return self::UNSUBSCRIBE;
    }

    private function unsubscribeFromMailingList(string $email): void
    {
        $updateSubscribers = array_filter($this->subject->subscribers, fn($mailBox) => $mailBox !== $email);
        $this->subject = SubjectDto::updateSubscribers($this->subject->toArray(), $updateSubscribers);
    }
}
