<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use PHPUnit\Framework\TestCase;

/**
 * T-172: HUB ist Wohnsiedlung (multi-instance, +100/Level), HQ ist Verwaltung
 * (unique, +25/Level). Tests prüfen beide Beiträge zum Pop-Cap.
 */
final class HubPopulationCapTest extends TestCase
{
    public function test_planet_starts_with_base_cap_100(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertSame(100, $planet->getPopulation()->getCap());
    }

    public function test_adding_hub_level_1_raises_cap_by_100(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));

        self::assertSame(200, $planet->getPopulation()->getCap());
    }

    public function test_adding_hub_level_3_raises_cap_by_300(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 3));

        self::assertSame(400, $planet->getPopulation()->getCap());
    }

    public function test_multiple_hubs_stack(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 2));

        // T-172: Base 100 + (1*100) + (2*100) = 400
        self::assertSame(400, $planet->getPopulation()->getCap());
    }

    public function test_hq_level_1_raises_cap_by_25(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HQ, 1));

        self::assertSame(125, $planet->getPopulation()->getCap());
    }

    public function test_hq_plus_hub_stack(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HQ, 2));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 3));

        // Base 100 + HQ L2 (50) + HUB L3 (300) = 450
        self::assertSame(450, $planet->getPopulation()->getCap());
    }

    public function test_non_hub_buildings_do_not_affect_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 5));

        self::assertSame(100, $planet->getPopulation()->getCap());
    }

    public function test_recalculate_after_level_change_must_be_explicit(): void
    {
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding($hub);
        self::assertSame(200, $planet->getPopulation()->getCap());

        $hub->setLevel(3);
        self::assertSame(200, $planet->getPopulation()->getCap(), 'cap is stale until explicit recalc');

        $planet->recalculatePopulationCap();
        self::assertSame(400, $planet->getPopulation()->getCap());
    }

    public function test_recalc_lowers_cap_clamps_total(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 2);
        $planet->addBuilding($hub);
        $planet->getPopulation()->grow(280);
        self::assertSame(280, $planet->getPopulation()->getTotal());

        $hub->setLevel(0);
        $planet->recalculatePopulationCap();

        // base 100 + 0 = 100 → total clamped 280→100
        self::assertSame(100, $planet->getPopulation()->getCap());
        self::assertSame(100, $planet->getPopulation()->getTotal());
    }
}
