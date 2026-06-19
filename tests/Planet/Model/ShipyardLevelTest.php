<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ShipyardLevelTest extends TestCase
{
    public function test_planet_without_shipyard_has_level_zero(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertFalse($planet->hasShipyard());
        self::assertSame(0, $planet->getShipyardLevel());
    }

    public function test_single_shipyard_level_2(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 2));

        self::assertTrue($planet->hasShipyard());
        self::assertSame(2, $planet->getShipyardLevel());
    }

    public function test_returns_highest_level_when_multiple_shipyards(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 4));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 2));

        self::assertSame(4, $planet->getShipyardLevel());
    }

    public function test_other_buildings_do_not_count(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 5));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 3));

        self::assertFalse($planet->hasShipyard());
        self::assertSame(0, $planet->getShipyardLevel());
    }

    public function test_unfinished_shipyard_does_not_count(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        $shipyard = new Building(BuildingId::generate(), BuildingType::SHIPYARD, 1);
        $shipyard->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($shipyard);

        $now = new DateTimeImmutable();
        self::assertFalse($planet->hasShipyard($now));
        self::assertSame(0, $planet->getShipyardLevel($now));
    }

    public function test_shipyard_ready_after_finishedAt(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        $shipyard = new Building(BuildingId::generate(), BuildingType::SHIPYARD, 3);
        $shipyard->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $planet->addBuilding($shipyard);

        $now = new DateTimeImmutable();
        self::assertTrue($planet->hasShipyard($now));
        self::assertSame(3, $planet->getShipyardLevel($now));
    }
}
