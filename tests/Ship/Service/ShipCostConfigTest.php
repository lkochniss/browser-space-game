<?php

declare(strict_types=1);

namespace App\Tests\Ship\Service;

use App\Resource\ValueObject\ResourceType;
use App\Ship\Service\ShipCostConfig;
use App\Ship\ValueObject\ShipType;
use PHPUnit\Framework\TestCase;

final class ShipCostConfigTest extends TestCase
{
    public function test_generic_ship_cost_matches_t012_baseline(): void
    {
        $config = new ShipCostConfig();

        self::assertSame(
            [ResourceType::IRON_BAR->value => 100],
            $config->getResourceCost(ShipType::GENERIC),
        );
        self::assertSame(20, $config->getPopulationCost(ShipType::GENERIC));
        self::assertSame(1800, $config->getDurationSeconds(ShipType::GENERIC));
    }

    public function test_colony_ship_is_strategic_tier(): void
    {
        $config = new ShipCostConfig();

        self::assertSame(
            [ResourceType::IRON_BAR->value => 300],
            $config->getResourceCost(ShipType::COLONY_SHIP),
        );
        self::assertSame(50, $config->getPopulationCost(ShipType::COLONY_SHIP));
        self::assertSame(3600, $config->getDurationSeconds(ShipType::COLONY_SHIP));
    }

    public function test_transport_small_is_intra_system_class(): void
    {
        $config = new ShipCostConfig();

        self::assertSame(
            [ResourceType::IRON_BAR->value => 150],
            $config->getResourceCost(ShipType::TRANSPORT_SMALL),
        );
        self::assertSame(15, $config->getPopulationCost(ShipType::TRANSPORT_SMALL));
        self::assertSame(1800, $config->getDurationSeconds(ShipType::TRANSPORT_SMALL));
    }

    public function test_transport_medium_costs_aluminum_too(): void
    {
        $config = new ShipCostConfig();
        $cost = $config->getResourceCost(ShipType::TRANSPORT_MEDIUM);

        self::assertSame(400, $cost[ResourceType::IRON_BAR->value]);
        self::assertSame(50, $cost[ResourceType::ALUMINUM_ORE->value]);
        self::assertSame(30, $config->getPopulationCost(ShipType::TRANSPORT_MEDIUM));
    }

    public function test_transport_large_is_heavy_hauler(): void
    {
        $config = new ShipCostConfig();
        $cost = $config->getResourceCost(ShipType::TRANSPORT_LARGE);

        self::assertSame(1000, $cost[ResourceType::IRON_BAR->value]);
        self::assertSame(200, $cost[ResourceType::ALUMINUM_ORE->value]);
        self::assertSame(50, $cost[ResourceType::TITANIUM_ORE->value]);
        self::assertSame(100, $config->getPopulationCost(ShipType::TRANSPORT_LARGE));
        self::assertSame(7200, $config->getDurationSeconds(ShipType::TRANSPORT_LARGE));
    }
}
