<?php

declare(strict_types=1);

namespace App\Tests\Fleet\Service;

use App\Fleet\Service\FleetMovementConfig;
use PHPUnit\Framework\TestCase;

final class FleetMovementConfigTest extends TestCase
{
    public function test_intra_system_baseline(): void
    {
        $config = new FleetMovementConfig();

        self::assertSame(1800, $config->getBaseDurationSeconds(true));
        self::assertSame(1800, $config->computeDurationSeconds(true, 1.0));
    }

    public function test_inter_system_baseline(): void
    {
        $config = new FleetMovementConfig();

        self::assertSame(14400, $config->getBaseDurationSeconds(false));
        self::assertSame(14400, $config->computeDurationSeconds(false, 1.0));
    }

    public function test_slow_fleet_takes_longer(): void
    {
        $config = new FleetMovementConfig();

        // TRANSPORT_LARGE (0.6) → 1800 / 0.6 = 3000
        self::assertSame(3000, $config->computeDurationSeconds(true, 0.6));
    }

    public function test_fast_fleet_takes_less(): void
    {
        $config = new FleetMovementConfig();

        // TRANSPORT_SMALL (1.2) → 1800 / 1.2 = 1500
        self::assertSame(1500, $config->computeDurationSeconds(true, 1.2));
    }

    public function test_minimum_duration_60s(): void
    {
        $config = new FleetMovementConfig();

        // Mit unrealistisch hoher Speed darf Duration nicht unter 60s fallen.
        self::assertSame(60, $config->computeDurationSeconds(true, 100.0));
    }

    public function test_zero_or_negative_speed_defaults_to_one(): void
    {
        $config = new FleetMovementConfig();

        self::assertSame(1800, $config->computeDurationSeconds(true, 0.0));
        self::assertSame(1800, $config->computeDurationSeconds(true, -2.0));
    }
}
