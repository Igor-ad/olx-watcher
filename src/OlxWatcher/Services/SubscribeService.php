<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\DTO\SubjectFactory;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class SubscribeService extends BaseService
{
    const NEW_SUBSCRIBE = 'Subscription to the resource has been completed.';
    const SUBSCRIBE = 'A subscription to this resource has already been completed.';
    const UNSUBSCRIBE = 'Unsubscribe from the resource is complete.';

    /**
     * @throws WatcherException
     */
    public function subscribe(string $url, string $email): string
    {
        if ($this->subject) {
            $message = $this->addNewSubscriber($url, $email);
        } else {
            $price = $this->getPrice($url);
            $this->subject = SubjectFactory::createFromRequest($price, $email);
            $message = self::NEW_SUBSCRIBE;
        }
        $this->cache->set($url, $this->subject->toArray());

        return $message;
    }

    protected function addNewSubscriber(string $url, string $email): string
    {
        if (in_array($email, $this->subject->subscribers)) {
            $this->logger->notice(self::SUBSCRIBE, [$email, $url]);

            return self::SUBSCRIBE;
        } else {
            $this->subject->subscribers[] = $email;
            $this->logger->notice(self::NEW_SUBSCRIBE, [$email, $url]);

            return self::NEW_SUBSCRIBE;
        }
    }

    public function unsubscribe(string $url, string $email): string
    {
        if ($this->subject) {
            $this->unsubscribeFromMailingList($url, $email);
        }
        $this->logger->notice(self::UNSUBSCRIBE, [$email, $url]);

        return self::UNSUBSCRIBE;
    }

    private function unsubscribeFromMailingList(string $url, string $email): void
    {
        $updateSubscribers = array_filter(
            $this->subject->subscribers,
            fn($mailBox) => $mailBox !== $email
        );
        $this->subject->subscribers = $updateSubscribers;
        $this->cache->set($url, $this->subject->toArray());
    }
}
