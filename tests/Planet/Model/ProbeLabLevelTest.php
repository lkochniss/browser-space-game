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

final class ProbeLabLevelTest extends TestCase
{
    public function test_planet_without_probe_lab_has_level_zero(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertFalse($planet->hasProbeLab());
        self::assertSame(0, $planet->getProbeLabLevel());
    }

    public function test_single_probe_lab_level_2(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::PROBE_LAB, 2));

        self::assertTrue($planet->hasProbeLab());
        self::assertSame(2, $planet->getProbeLabLevel());
    }

    public function test_returns_highest_level_when_multiple_probe_labs(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::PROBE_LAB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::PROBE_LAB, 5));

        self::assertSame(5, $planet->getProbeLabLevel());
    }

    public function test_other_buildings_do_not_count(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 3));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 5));

        self::assertFalse($planet->hasProbeLab());
        self::assertSame(0, $planet->getProbeLabLevel());
    }

    public function test_unfinished_probe_lab_does_not_count(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        $lab = new Building(BuildingId::generate(), BuildingType::PROBE_LAB, 1);
        $lab->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($lab);

        $now = new DateTimeImmutable();
        self::assertFalse($planet->hasProbeLab($now));
        self::assertSame(0, $planet->getProbeLabLevel($now));
    }
}
