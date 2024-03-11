<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

use Autodoctor\OlxWatcher\CacheFileService;
use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class Mailer
{
    protected array $config = [];
    protected array $subscribe = [];
    protected array $updatedKeys = [];
    protected bool $isSend = true;

    /**
     * @throws WatcherException
     */
    public function __construct()
    {
        $this->config = parse_ini_file(
            FilesEnum::CONFIG_FILE, true
        );
        $this->subscribe = CacheFileService::get(FilesEnum::SUBSCRIBE_FILE);
        $this->updatedKeys = CacheFileService::get(FilesEnum::UPDATE_KEYS_FILE);
    }

    /**
     * @throws WatcherException
     */
    public function __invoke(): int
    {
        return $this->sender();
    }

    public function sendMail(string $subscriber, string $url): bool
    {
        $to = $subscriber;
        $subject = $this->config['config.mail']['subject'];
        $message = $this->messageFormatter($url);
        $headers[] = 'From: ' . $this->config['config.mail']['sender'];
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    protected function createMailingList(): void
    {
        if (isset($this->updatedKeys)) {
            foreach ($this->updatedKeys as $url) {
                foreach ($this->subscribe[$url]['subscribers'] as $subscriber) {
                    $this->checkSender($subscriber, $url);
                }
            }
        }
    }

    protected function messageFormatter(string $url): string
    {
        return $this->config['config.mail']['message']
            . $url . "\r\n" . 'New price: ' . $this->subscribe[$url]['last_price'];
    }

    public function checkSender(string $subscriber, string $url): int
    {
        if (!$this->sendMail($subscriber, $url)) {
            $this->isSend = false;

            echo 'Mail not end';
        }
        return 0;
    }

    /**
     * @throws WatcherException
     */
    public function sender(): int
    {
        $this->createMailingList();
        if ($this->isSend) {
            return 0;
        }
        throw new WatcherException('Error send mail');
    }
}
