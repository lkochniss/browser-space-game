<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class StorageCapacityTest extends TestCase
{
    public function test_empty_planet_has_only_base_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertSame(100, $planet->getStorageCapacity(ResourceType::IRON_ORE));
        self::assertSame(500, $planet->getStorageCapacity(ResourceType::WATER));
        self::assertSame(100, $planet->getStorageCapacity(ResourceType::IRON_BAR));
    }

    public function test_iron_mine_l1_adds_100_to_iron_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1));

        self::assertSame(200, $planet->getStorageCapacity(ResourceType::IRON_ORE));
        // Doesn't affect other resources
        self::assertSame(100, $planet->getStorageCapacity(ResourceType::COAL));
    }

    public function test_iron_mine_l3_adds_300(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 3));

        self::assertSame(400, $planet->getStorageCapacity(ResourceType::IRON_ORE));
    }

    public function test_iron_storage_l1_adds_1000(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_STORAGE, 1));

        self::assertSame(1100, $planet->getStorageCapacity(ResourceType::IRON_ORE));
    }

    public function test_water_tank_l2_adds_4000(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::WATER_TANK, 2));

        self::assertSame(4500, $planet->getStorageCapacity(ResourceType::WATER));
    }

    public function test_hub_adds_renewable_storage(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));

        // base 500 + 200/level
        self::assertSame(700, $planet->getStorageCapacity(ResourceType::WATER));
        self::assertSame(700, $planet->getStorageCapacity(ResourceType::FOOD));
        self::assertSame(700, $planet->getStorageCapacity(ResourceType::OXYGEN));
    }

    public function test_smelter_adds_iron_bar_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 1));

        self::assertSame(200, $planet->getStorageCapacity(ResourceType::IRON_BAR));
        // Smelter doesn't contribute to ore storage
        self::assertSame(100, $planet->getStorageCapacity(ResourceType::IRON_ORE));
    }

    public function test_multiple_buildings_stack(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 2));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_STORAGE, 3));

        // 100 base + (2 × 100) + (3 × 1000) = 3300
        self::assertSame(3300, $planet->getStorageCapacity(ResourceType::IRON_ORE));
    }
}
