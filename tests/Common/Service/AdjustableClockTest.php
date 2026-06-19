<?php

declare(strict_types=1);

namespace App\Tests\Common\Service;

use App\Common\Service\AdjustableClock;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AdjustableClockTest extends TestCase
{
    public function test_default_constructor_is_now_ish(): void
    {
        $clock = new AdjustableClock();
        $diff = abs(time() - $clock->now()->getTimestamp());
        self::assertLessThan(2, $diff, 'default-now sollte fast aktuelle Zeit sein');
    }

    public function test_explicit_start_time(): void
    {
        $start = new DateTimeImmutable('2026-01-01 12:00:00');
        $clock = new AdjustableClock($start);
        self::assertSame($start->getTimestamp(), $clock->now()->getTimestamp());
    }

    public function test_advance_seconds(): void
    {
        $start = new DateTimeImmutable('2026-01-01 00:00:00');
        $clock = new AdjustableClock($start);
        $clock->advanceSeconds(3600);
        self::assertSame('2026-01-01 01:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function test_advance_via_interval(): void
    {
        $start = new DateTimeImmutable('2026-01-01 00:00:00');
        $clock = new AdjustableClock($start);
        $clock->advance(new DateInterval('P1D'));
        self::assertSame('2026-01-02 00:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function test_advance_seconds_clamps_to_zero(): void
    {
        $start = new DateTimeImmutable('2026-01-01 00:00:00');
        $clock = new AdjustableClock($start);
        $clock->advanceSeconds(-1000);
        // negative wird zu 0 geclampt → keine Bewegung
        self::assertSame('2026-01-01 00:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function test_reset(): void
    {
        $clock = new AdjustableClock(new DateTimeImmutable('2026-01-01'));
        $clock->reset(new DateTimeImmutable('2027-06-15'));
        self::assertSame('2027-06-15', $clock->now()->format('Y-m-d'));
    }
}
