<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class SubscribeService extends BaseService
{
    const NEW_SUBSCRIBE = 'Subscription to the resource has been completed.';
    const SUBSCRIBE = 'A subscription to this resource has already been completed.';
    const UNSUBSCRIBE = 'Unsubscribe from the resource is complete.';

    /**
     * @throws WatcherException
     */
    public function subscribe(string $url, string $email): int
    {
        if ($this->subscribe) {
            $this->addNewSubscriber($url, $email);
        } else {
            $price = $this->getPrice($url);
            $this->subscribe = $this->subscribeResource($price, $email);
        }
        $this->cache->set($url, $this->subscribe);

        return 0;
    }

    protected function addNewSubscriber(string $url, string $email): void
    {
        if (in_array($email, $this->subscribe['subscribers'])) {

            $this->logger->notice(self::SUBSCRIBE, [$email, $url]);
        } else {
            $this->subscribe['subscribers'][] = $email;
            $this->logger->notice(self::NEW_SUBSCRIBE, [$email, $url]);
        }
    }

    protected function subscribeResource(string $price, string $email): array
    {
        return [
            'previous_price' => $price,
            'last_price' => $price,
            'previous_time' => date("Y-m-d H:i:s"),
            'last_time' => date("Y-m-d H:i:s"),
            'subscribers' => [$email]
        ];
    }

    public function unsubscribe(string $url, string $email): int
    {
        if ($this->subscribe) {
            $this->unsubscribeFromMailingList($url, $email);
        }
        $this->logger->notice(self::UNSUBSCRIBE, [$email, $url]);

        return 0;
    }

    private function unsubscribeFromMailingList(string $url, string $email): void
    {
        $updateSubscribers = array_filter(
            $this->subscribe['subscribers'], fn($mailBox) => $mailBox !== $email
        );
        $this->subscribe['subscribers'] = $updateSubscribers;
        $this->cache->set($url, $this->subscribe);
    }
}
