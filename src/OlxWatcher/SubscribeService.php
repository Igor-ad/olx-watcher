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

    public function __construct()
    {
        $this->email = $this->validateEmail();
        $this->url = $this->validateUrl();
        $this->subscribe = CacheFileService::get(FilesEnum::SUBSCRIBE_FILE);;
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
        $parcer = ParserFactory::getParser();
        $parcer->setTargetUrl($this->url);
        $parcer->parse();

        return $parcer->getPrice();
    }

    public function subscribe(): int
    {
        if (array_key_exists($this->url, $this->subscribe)) {
            $this->addNewSubscriber();
        } else {
            $this->subscribe[$this->url] = self::subscribeResource($this->getPrice());
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
}
