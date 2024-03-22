<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Database\CacheInterface;
use Autodoctor\OlxWatcher\Exceptions\MailerException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Mailer implements LoggerAwareInterface
{
    use Formatter, LoggerAwareTrait;

    const NOTICE = 'There is no mailing list.';

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
        if ($this->updatedKeys === []) {
            echo self::NOTICE . PHP_EOL;
            $this->logger->notice(self::NOTICE);

            return 0;
        }
        return $this->sender();
    }

    public function sendMail(string $subscriber, string $url): bool
    {
        $to = $subscriber;
        $subject = $this->config['mail']['subject'];
        $message = $this->priceUpdateMessage($url);
        $headers[] = 'From: ' . $this->config['mail']['sender'];
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        return mail($to, $subject, $message, implode(self::RN, $headers));
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
            echo 'The email may have been sent.' . PHP_EOL;
            return 0;
        }
        throw new MailerException('Error sending email.');
    }
}
