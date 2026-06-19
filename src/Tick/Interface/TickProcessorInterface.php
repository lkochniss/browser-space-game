<?php

declare(strict_types=1);

namespace App\Tick\Interface;

use App\Planet\Model\Planet;
use DateTimeImmutable;

interface TickProcessorInterface
{
    /**
     * @param DateTimeImmutable|null $now Current game-clock time. If null, processors should
     *                                    treat all buildings as ready (T-062 isReady-fallback).
     */
    public function process(Planet $planet, ?DateTimeImmutable $now = null): void;
}
