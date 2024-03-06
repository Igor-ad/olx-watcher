<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;

trait CurlTrait
{
    public function getUri(string $targetUrl): string
    {
        $init = curl_init();
        curl_setopt_array($init, $this->curlSetup($targetUrl));
        $output = curl_exec($init);
        $getInfo = curl_getinfo($init);
        $errNo = curl_errno($init);
        curl_close($init);
        
        if (!$errNo && ($getInfo['http_code'] >= 200 || $getInfo['http_code'] < 300)) {
            return $output;
        }
        throw new WatcherException('Target URL not available, error - ' . $errNo);
    }

    protected function curlSetup(string $targetUrl): array
    {
        return [
            CURLOPT_URL => $targetUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIME_OUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECTTIMEOUT,
        ];
    }
}
