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

    const EMPTY_LIST = 'There is no mailing list.';
    const MAIL_SENT = 'The email may have been sent.';

    protected array $config = [];
    protected array $subscribe = [];
    protected array $updatedKeys = [];
    protected bool $isSent = true;

    /**
     * @throws WatcherException
     */
    public function __construct(
        protected CacheInterface $cache
    )
    {
        $this->config = Configurator::config();
        $this->updatedKeys = $cache->get('updated') ?? [];
    }

    /**
     * @throws MailerException
     */
    public function __invoke(): int
    {
        if ($this->updatedKeys === []) {
            $this->logger->notice(self::EMPTY_LIST);

            return 0;
        }
        return $this->sender();
    }

    public function sendMail(string $email, string $url): bool
    {
        $to = $email;
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
        foreach ($this->updatedKeys as $url) {
            $this->subscribe = $this->cache->get($url);
            foreach ($this->subscribe['subscribers'] as $email) {
                $this->send($email, $url);
            }
        }
    }

    /**
     * @throws MailerException
     */
    public function send(string $email, string $url): int
    {
        if ($this->sendMail($email, $url)) {
            return 0;
        }
        $this->isSent = false;

        throw new MailerException(sprintf(
            'The email is not sent to the recipient: %s', $email
        ));
    }

    /**
     * @throws MailerException
     */
    public function sender(): int
    {
        $this->createMailingList();

        if ($this->isSent) {
            $this->logger->notice(self::MAIL_SENT);

            return 0;
        }
        throw new MailerException('Error sending email.');
    }
}
