<?php

declare(strict_types=1);

namespace App\Tests\Probe\Service;

use App\Probe\Service\ProbeCostConfig;
use App\Probe\ValueObject\ProbeType;
use App\Resource\ValueObject\ResourceType;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ProbeCostConfigTest extends TestCase
{
    public function test_system_probe_is_cheap_and_fast(): void
    {
        $config = new ProbeCostConfig();
        $cost = $config->getResourceCost(ProbeType::SYSTEM);

        self::assertSame([ResourceType::IRON_BAR->value => 30], $cost);
        self::assertSame(600, $config->getDurationSeconds(ProbeType::SYSTEM));
    }

    public function test_orbital_probe_costs_more(): void
    {
        $config = new ProbeCostConfig();
        $cost = $config->getResourceCost(ProbeType::ORBITAL);

        self::assertSame(80, $cost[ResourceType::IRON_BAR->value]);
        self::assertSame(30, $cost[ResourceType::SILICON->value]);
        self::assertSame(1200, $config->getDurationSeconds(ProbeType::ORBITAL));
    }

    public function test_deep_scan_probe_is_endgame_tier(): void
    {
        $config = new ProbeCostConfig();
        $cost = $config->getResourceCost(ProbeType::DEEP_SCAN);

        self::assertSame(200, $cost[ResourceType::IRON_BAR->value]);
        self::assertSame(80, $cost[ResourceType::SILICON->value]);
        self::assertSame(50, $cost[ResourceType::COPPER_ORE->value]);
        self::assertSame(3600, $config->getDurationSeconds(ProbeType::DEEP_SCAN));
    }
}
