<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use PHPUnit\Framework\TestCase;

final class HubPopulationCapTest extends TestCase
{
    public function test_planet_starts_with_base_cap_100(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertSame(100, $planet->getPopulation()->getCap());
    }

    public function test_adding_hub_level_1_raises_cap_by_50(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));

        self::assertSame(150, $planet->getPopulation()->getCap());
    }

    public function test_adding_hub_level_3_raises_cap_by_150(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 3));

        self::assertSame(250, $planet->getPopulation()->getCap());
    }

    public function test_multiple_hubs_stack(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 2));

        // Base 100 + (1*50) + (2*50) = 250
        self::assertSame(250, $planet->getPopulation()->getCap());
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
        self::assertSame(150, $planet->getPopulation()->getCap());

        // Manuelles Level-Setzen umgeht addBuilding → cap stale, bis recalculate gerufen wird
        $hub->setLevel(3);
        self::assertSame(150, $planet->getPopulation()->getCap(), 'cap is stale until explicit recalc');

        $planet->recalculatePopulationCap();
        self::assertSame(250, $planet->getPopulation()->getCap());
    }

    public function test_recalc_lowers_cap_clamps_total(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 2);
        $planet->addBuilding($hub);
        $planet->getPopulation()->grow(180);
        self::assertSame(180, $planet->getPopulation()->getTotal());

        $hub->setLevel(0);
        $planet->recalculatePopulationCap();

        // base 100 + 0 = 100 → total clamped 180→100
        self::assertSame(100, $planet->getPopulation()->getCap());
        self::assertSame(100, $planet->getPopulation()->getTotal());
    }
}
