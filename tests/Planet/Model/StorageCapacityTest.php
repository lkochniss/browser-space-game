<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

/**
 * T-177 Generic-Storage-Refactor. Per-Resource-Cap (T-061) ersetzt durch
 * Volume-Cap in m³ über `Planet::getStorageVolumeCapacity()` +
 * `getStorageVolumeUsed()` + `maxAddableQuantity()`.
 *
 * Legacy `getStorageCapacity(R)` ist Approximation: `current + maxAddable(R)`.
 */
final class StorageCapacityTest extends TestCase
{
    public function test_empty_planet_has_base_volume(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        // T-177: BASE_VOLUME_CAPACITY = 5000 m³
        self::assertSame(5000, $planet->getStorageVolumeCapacity());
        self::assertSame(0, $planet->getStorageVolumeUsed());
        self::assertSame(5000, $planet->getStorageVolumeFree());
    }

    public function test_warehouse_l1_adds_500(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::WAREHOUSE, 1));

        self::assertSame(5500, $planet->getStorageVolumeCapacity());
    }

    public function test_warehouse_l3_stacks(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::WAREHOUSE, 3));

        self::assertSame(6500, $planet->getStorageVolumeCapacity());
    }

    public function test_hq_l1_adds_25(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HQ, 1));

        self::assertSame(5025, $planet->getStorageVolumeCapacity());
    }

    public function test_iron_mine_adds_50(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1));

        self::assertSame(5050, $planet->getStorageVolumeCapacity());
    }

    public function test_hub_contributes_zero_volume(): void
    {
        // T-172: HUB ist reines Wohngebäude, kein Storage
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 5));

        self::assertSame(5000, $planet->getStorageVolumeCapacity());
    }

    public function test_volume_used_sums_resources_via_multi(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));    // 100 × 2.0 = 200
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 50));         // 50  × 1.8 = 90
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 200));       // 200 × 1.0 = 200

        self::assertSame(490, $planet->getStorageVolumeUsed());
    }

    public function test_pop_counts_against_volume(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->getPopulation()->grow(50); // 50 × 10.0 = 500 m³

        self::assertSame(500, $planet->getStorageVolumeUsed());
    }

    public function test_can_add_item_when_room_available(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        // Base 5000 m³, IRON_ORE = 2 m³ → 2500 Einheiten max
        self::assertTrue($planet->canAddItem(ResourceType::IRON_ORE, 100));
        self::assertSame(100, $planet->maxAddableQuantity(ResourceType::IRON_ORE, 100));
    }

    public function test_max_addable_clamps_to_available_volume(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::WAREHOUSE, 1));

        // 5500 m³ frei → IRON_ORE (2 m³): max 2750
        self::assertSame(2750, $planet->maxAddableQuantity(ResourceType::IRON_ORE, 999999));
    }

    public function test_can_add_item_false_when_full(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        // Belege das gesamte Volumen mit IRON_ORE (5000 / 2.0 = 2500)
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 2500));

        self::assertSame(0, $planet->getStorageVolumeFree());
        self::assertFalse($planet->canAddItem(ResourceType::IRON_ORE, 1));
    }
}
