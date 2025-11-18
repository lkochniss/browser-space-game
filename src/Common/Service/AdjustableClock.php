<?php

namespace App\Common\Service;

use App\Common\Interface\ClockInterface;
use DateTimeImmutable;

class AdjustableClock implements ClockInterface
{
    private DateTimeImmutable $currentTime;

    public function __construct(DateTimeImmutable $startTime)
    {
        $this->currentTime = $startTime;
    }

    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }

    public function advance(int $seconds): void
    {
        $this->currentTime = $this->currentTime->modify("+{$seconds} seconds");
    }
}
