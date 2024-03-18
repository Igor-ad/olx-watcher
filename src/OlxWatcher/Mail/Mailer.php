<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

use Autodoctor\OlxWatcher\CacheInterface;
use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\MailerException;
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
    public function __construct(
        protected CacheInterface $cache
    )
    {
        $this->config = Configurator::config();
        $this->subscribe = $cache->get('subscribe');
        $this->updatedKeys = $cache->get('updated');
    }

    /**
     * @throws MailerException
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

    /**
     * @throws MailerException
     */
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

    /**
     * @throws MailerException
     */
    public function checkSender(string $subscriber, string $url): int
    {
        if (!$this->sendMail($subscriber, $url)) {
            $this->isSend = false;

            throw new MailerException(sprintf(
                'The email is not sent to the recipient: %s', $subscriber
            ));
        }
        return 0;
    }

    /**
     * @throws MailerException
     */
    public function sender(): int
    {
        $this->createMailingList();
        if ($this->isSend) {
            return 0;
        }
        throw new MailerException('Error sending email');
    }
}
