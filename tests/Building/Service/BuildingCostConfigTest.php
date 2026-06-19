<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\BuildingCostConfig;
use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use LogicException;
use PHPUnit\Framework\TestCase;

final class BuildingCostConfigTest extends TestCase
{
    public function test_initial_cost_is_base(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::IRON_MINE);

        self::assertSame(50, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(5, $cost->populationCost);
    }

    public function test_level_0_equals_initial(): void
    {
        $config = new BuildingCostConfig();
        self::assertEquals(
            $config->getCost(BuildingType::IRON_MINE),
            $config->getCost(BuildingType::IRON_MINE, currentLevel: 0),
        );
    }

    public function test_upgrade_from_level_1_doubles_cost(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::IRON_MINE, currentLevel: 1);

        // base 50 * 2^1 = 100; pop 5 * 2 = 10
        self::assertSame(100, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(10, $cost->populationCost);
    }

    public function test_upgrade_from_level_5_uses_32x_multiplier(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::HUB, currentLevel: 5);

        // base hub: 100 Iron + 50 Coal + 10 Pop. *32:
        self::assertSame(3200, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(1600, $cost->resources[ResourceType::COAL->value]);
        self::assertSame(320, $cost->populationCost);
    }

    public function test_negative_level_throws(): void
    {
        $config = new BuildingCostConfig();

        $this->expectException(LogicException::class);
        $config->getCost(BuildingType::IRON_MINE, currentLevel: -1);
    }

    public function test_shipyard_cost_at_level_0(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::SHIPYARD);

        self::assertSame(500, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(100, $cost->resources[ResourceType::COAL->value]);
        self::assertSame(200, $cost->resources[ResourceType::ALUMINUM_ORE->value]);
        self::assertSame(50, $cost->resources[ResourceType::TITANIUM_ORE->value]);
        self::assertSame(30, $cost->populationCost);
    }

    public function test_shipyard_upgrade_doubles_at_level_1(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::SHIPYARD, currentLevel: 1);

        self::assertSame(1000, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(200, $cost->resources[ResourceType::COAL->value]);
        self::assertSame(400, $cost->resources[ResourceType::ALUMINUM_ORE->value]);
        self::assertSame(100, $cost->resources[ResourceType::TITANIUM_ORE->value]);
        self::assertSame(60, $cost->populationCost);
    }

    public function test_probe_lab_cost_at_level_0(): void
    {
        $config = new BuildingCostConfig();
        $cost = $config->getCost(BuildingType::PROBE_LAB);

        self::assertSame(200, $cost->resources[ResourceType::IRON_ORE->value]);
        self::assertSame(100, $cost->resources[ResourceType::SILICON->value]);
        self::assertSame(50, $cost->resources[ResourceType::COPPER_ORE->value]);
        self::assertSame(15, $cost->populationCost);
    }
}
