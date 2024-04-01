<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\MailerException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Services\BaseService;

class Mailer extends BaseService
{
    use Formatter;

    const EMPTY_LIST = 'There is no mailing list.';
    const MAIL_SENT = 'The email may have been sent.';

    protected array $config = [];
    protected array $updatedKeys = [];
    protected bool $wasSent = true;

    /**
     * @throws \RedisException|WatcherException
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = Configurator::config();
        $this->updatedKeys = $this->cache->get('updated') ?? [];
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
        $message = $this->formatMessage($url, $email);
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
            $this->setSubscribe($url);
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
        $this->wasSent = false;

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

        if ($this->wasSent) {
            $this->logger->notice(self::MAIL_SENT);

            return 0;
        }
        throw new MailerException('Error sending email.');
    }
}
