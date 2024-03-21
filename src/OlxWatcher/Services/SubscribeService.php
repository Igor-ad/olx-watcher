<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Exceptions\ValidateException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\ParserFactory;
use Autodoctor\OlxWatcher\Validator\ValidateService;

class SubscribeService
{
    protected array $subscribe = [];
    protected string $email;
    protected string $url;
    protected string|bool $status;

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
        $this->status = $validData['status'];
        $this->subscribe = $this->cache->get('subscribe');
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
        if ($this->status === true) {

            return $this->unsubscribe();

        } else {

            if (array_key_exists($this->url, $this->subscribe)) {
                $this->addNewSubscriber();
            } else {
                $this->subscribe[$this->url] = $this->subscribeResource($this->getPrice());
            }
        }
        $this->cache->set('subscribe', $this->subscribe);

        return 0;
    }

    protected function addNewSubscriber(): void
    {
        if (in_array($this->email, $this->subscribe[$this->url]['subscribers'])) {
            echo 'You are already subscribed to this resource.' . "\n\r";
        } else {
            $this->subscribe[$this->url]['subscribers'][] = $this->email;
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
        foreach ($this->subscribe as $url => $item) {
            $item['subscribers'] = array_filter(
                $item['subscribers'],
                fn($email) => $email != $this->email
            );
            $this->subscribe[$url] = $item;
            $this->cache->set('subscribe', $this->subscribe);
        }
        echo 'You have unsubscribed from this resource.' . PHP_EOL;
        return 0;
    }
}
