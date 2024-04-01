<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Controllers;

use Autodoctor\OlxWatcher\Exceptions\ValidateException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Services\SubscribeService;
use Autodoctor\OlxWatcher\Validator\ValidateService;

class SubscribeController
{
    protected array $validData;

    /**
     * @throws ValidateException
     */
    public function __construct(
        private SubscribeService $service,
    )
    {
        $this->validData = $this->getValidData();
        $this->service->setSubscribe($this->validData['url']);
    }

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        if ($this->validData['status'] === true) {
            return $this->unsubscribe();
        } else {
            return $this->subscribe();
        }
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
     * @throws ValidateException
     */
    public function getValidData(): array
    {
        return ValidateService::validated($this->rules());
    }

    /**
     * @throws WatcherException
     */
    public function subscribe(): int
    {
        return $this->service->subscribe(
            $this->validData['url'],
            $this->validData['email'],
        );
    }

    public function unsubscribe(): int
    {
        return $this->service->unsubscribe(
            $this->validData['url'],
            $this->validData['email'],
        );
    }
}
