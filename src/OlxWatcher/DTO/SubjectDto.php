<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\DTO;

readonly class SubjectDto
{
    public function __construct(
        public string $previousPrice,
        public string $lastPrice,
        public string $previousTime,
        public string $lastTime,
        public array  $subscribers,
        public bool   $hasUpdate = false,
    ) {}

    public function toArray(): array
    {
        return [
            'previous_price' => $this->previousPrice,
            'last_price' => $this->lastPrice,
            'previous_time' => $this->previousTime,
            'last_time' => $this->lastTime,
            'subscribers' => $this->subscribers,
            'has_update' => $this->hasUpdate,
        ];
    }
}
