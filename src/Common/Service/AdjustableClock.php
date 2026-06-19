<?php

namespace App\Common\Service;

use App\Common\Interface\ClockInterface;
use DateInterval;
use DateTimeImmutable;

class AdjustableClock implements ClockInterface
{
    private DateTimeImmutable $currentTime;

    public function __construct(?DateTimeImmutable $startTime = null)
    {
        $this->currentTime = $startTime ?? new DateTimeImmutable();
    }

    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }

    public function advance(DateInterval $interval): void
    {
        $this->currentTime = $this->currentTime->add($interval);
    }

    public function advanceSeconds(int $seconds): void
    {
        $this->currentTime = $this->currentTime->add(new DateInterval(sprintf('PT%dS', max(0, $seconds))));
    }

    public function reset(DateTimeImmutable $start): void
    {
        $this->currentTime = $start;
    }

    public function diffInSeconds(ClockInterface $other): int
    {
        return (int) ($this->currentTime->getTimestamp() - $other->now()->getTimestamp());
    }
}
