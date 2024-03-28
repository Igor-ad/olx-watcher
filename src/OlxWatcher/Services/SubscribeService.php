<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Exceptions\ValidateException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\ParserFactory;
use Autodoctor\OlxWatcher\Validator\ValidateService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SubscribeService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const NEW_SUBSCRIBE = 'You subscribed to this resource.';
    const SUBSCRIBE = 'You are already subscribed to this resource.';
    const UNSUBSCRIBE = 'You have unsubscribed from this resource.';

    protected array $subscribe = [];
    protected string $email;
    protected string $url;

    /**
     * @var string|bool
     * If $unsubscribe === true, the SubscribeService::unsubscribe() method will run.
     */
    protected string|bool $unsubscribe;

    /**
     * @throws ValidateException|WatcherException
     */
    public function __construct(
        protected CacheInterface $cache,
    )
    {
        $validData = ValidateService::validated($this->rules());
        $this->email = $validData['email'];
        $this->url = $validData['url'];
        $this->unsubscribe = $validData['status'];
        $this->subscribe = $this->cache->get($this->url) ?? [];
    }

    private function rules(): array
    {
        return [
            'email' => [
                'filter' => FILTER_VALIDATE_EMAIL,
            ],
            'url' => [
                'filter' => FILTER_VALIDATE_URL,
                'flags' => FILTER_FLAG_PATH_REQUIRED,
            ],
            'status' => [
                'filter' => FILTER_CALLBACK,
                'options' => fn($value) => $value === 'unsubscribe' ? true : '',
            ],
        ];
    }

    /**
     * @throws WatcherException
     */
    protected function getPrice(): string
    {
        $parser = ParserFactory::getParser();
        $parser->setTargetUrl($this->url);
        $parser->parse();

        return $parser->getPrice();
    }

    /**
     * @throws WatcherException
     */
    public function subscribe(): int
    {
        if ($this->unsubscribe === true) {

            return $this->unsubscribe();
        } else {
            if ($this->subscribe) {
                $this->addNewSubscriber();
            } else {
                $this->subscribe = $this->subscribeResource($this->getPrice());
            }
            $this->cache->set($this->url, $this->subscribe);
        }

        return 0;
    }

    protected function addNewSubscriber(): void
    {
        if (in_array($this->email, $this->subscribe['subscribers'])) {
            $this->logger->notice(self::SUBSCRIBE, $this->toArray());
        } else {
            $this->subscribe['subscribers'][] = $this->email;
            $this->logger->notice(self::NEW_SUBSCRIBE, $this->toArray());
        }
    }

    protected function subscribeResource(string $price): array
    {
        return [
            'previous_price' => $price,
            'last_price' => $price,
            'previous_time' => date("Y-m-d H:i:s"),
            'last_time' => date("Y-m-d H:i:s"),
            'subscribers' => [$this->email]
        ];
    }

    protected function unsubscribe(): int
    {
        if ($this->subscribe) {
            $this->unsubscribeFromMailingList();
        }
        $this->logger->notice(self::UNSUBSCRIBE, $this->toArray());

        return 0;
    }

    private function unsubscribeFromMailingList(): void
    {
        $updateSubscribers = array_filter(
            $this->subscribe['subscribers'], fn($email) => $email != $this->email
        );
        $this->subscribe['subscribers'] = $updateSubscribers;
        $this->cache->set($this->url, $this->subscribe);
    }

    protected function toArray(): array
    {
        return [json_encode($this->unsubscribe), $this->email, $this->url];
    }
}
