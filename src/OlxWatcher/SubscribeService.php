<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\ValidateException;
use Autodoctor\OlxWatcher\Enums\FilesEnum;

class SubscribeService
{
    protected array $subscribe = [];
    protected string $email;
    protected string $url;
    protected bool $status = false;

    public function __construct()
    {
        $this->email = $this->validateEmail();
        $this->url = $this->validateUrl();
        $this->subscribe = CacheFileService::get(FilesEnum::SUBSCRIBE_FILE);
        $this->setStatus();
    }

    /**
     * @throws ValidateException
     */
    public function validateEmail(): string
    {
        $email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) : '';
        if ($email) {
            return $email;
        }
        throw new ValidateException('The email address entered is invalid.');
    }

    /**
     * @throws ValidateException
     */
    public function validateUrl(): string
    {
        $url = isset($_GET['url']) ? filter_var($_GET['url'], FILTER_VALIDATE_URL) : '';
        if ($url) {
            return $url;
        }
        throw new ValidateException('The entered Internet resource address is invalid.');
    }

    protected function getPrice(): string
    {
        $parser = ParserFactory::getParser();
        $parser->setTargetUrl($this->url);
        $parser->parse();

        return $parser->getPrice();
    }

    public function setStatus(): void
    {
        $this->status = isset($_GET['status']) && $_GET['status'] === 'unsubscribe';
    }

    public function subscribe(): int
    {
        if ($this->status) {
            return $this->unsubscribe();
        }

        if (array_key_exists($this->url, $this->subscribe)) {
            $this->addNewSubscriber();
        } else {
            $this->subscribe[$this->url] = $this->subscribeResource($this->getPrice());
        }
        CacheFileService::set(FilesEnum::SUBSCRIBE_FILE, $this->subscribe);

        return 0;
    }

    /**
     * to check the subscriber uncomment the following lines
     */
    protected function addNewSubscriber(): void
    {
//        if (in_array($this->email, $this->subscribe[$this->url]['subscribers'])) {
//            throw new WatcherException('You are already subscribed to this resource.');
//        }
        $this->subscribe[$this->url]['subscribers'][] = $this->email;
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

    public function unsubscribe(): int
    {
        foreach ($this->subscribe as $item) {
            unset($item['subscribers'][$this->email]);

        }
        return 0;
    }
}
