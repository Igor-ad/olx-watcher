<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Mail;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\MailerException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Services\AbstractService;

class Mailer extends AbstractService
{
    use Formatter;

    public const EMPTY_LIST = 'There is no mailing list.';
    public const MAIL_SENT = 'The email may have been sent.';

    protected array $config = [];
    protected bool $wasSent = true;

    /**
     * @throws \RedisException|WatcherException
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = Configurator::config();
        $this->setUpdatedKeys();
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

    public function setUpdatedKeys(): void
    {
        foreach ($this->subjectKeys as $url) {
            $subject = $this->cache->offsetGet($url);
            if ($subject->hasUpdate === true) {
                $this->updatedKeys[] = $url;
            }
        }
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
            $this->setSubject($url);
            foreach ($this->subject->subscribers as $email) {
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

        throw new MailerException(
            sprintf(
                'The email is not sent to the recipient: %s',
                $email
            )
        );
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
