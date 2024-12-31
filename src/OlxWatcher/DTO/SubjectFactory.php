<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\DTO;

class SubjectFactory
{
    public static function createFromArray(array $data): SubjectDto
    {
        return new SubjectDto(
            previousPrice: $data['previous_price'],
            lastPrice: $data['last_price'],
            previousTime: $data['previous_time'],
            lastTime: $data['last_time'],
            subscribers: $data['subscribers'],
            hasUpdate: $data['has_update'],
        );
    }

    public static function createFromRequest(string $price, string $email): SubjectDto
    {
        $dateTime = (new \DateTime('now'))->format('Y-m-d H:i:s');

        return new SubjectDto(
            previousPrice: $price,
            lastPrice: $price,
            previousTime: $dateTime,
            lastTime: $dateTime,
            subscribers: [$email],
            hasUpdate: false,
        );
    }

    public static function updatePrice(array $data, string $price): SubjectDto
    {
        return new SubjectDto(
            previousPrice: $data['last_price'],
            lastPrice: $price,
            previousTime: $data['last_time'],
            lastTime: (new \DateTime('now'))->format('Y-m-d H:i:s'),
            subscribers: $data['subscribers'],
            hasUpdate: true,
        );
    }

    public static function changeUpdateFlag(array $data): SubjectDto
    {
        return new SubjectDto(
            previousPrice: $data['previous_price'],
            lastPrice: $data['last_price'],
            previousTime: $data['previous_time'],
            lastTime: $data['last_time'],
            subscribers: $data['subscribers'],
            hasUpdate: false,
        );
    }
}
