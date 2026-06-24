<?php

declare(strict_types=1);

namespace App\Tests\Player\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use PHPUnit\Framework\TestCase;

/**
 * T-068 Sensor-Range-Helper für T-074 Pirate-Spawn-Notification-Hook.
 * Foundation: Range = Sensor-Level; gilt nur im selben SolarSystem.
 */
final class SensorRangeTest extends TestCase
{
    public function test_no_sensor_returns_false(): void
    {
        $player = new Player(PlayerId::generate());
        $sys = $this->makeSystem();
        self::assertFalse($player->hasSensorInSystem($sys, 1));
    }

    public function test_sensor_in_same_system_returns_true(): void
    {
        $sys = $this->makeSystem();
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->setSolarSystem($sys);
        $player->claimPlanet($planet);

        $sensor = new Building(BuildingId::generate(), BuildingType::SENSOR_ARRAY, 2);
        $sensor->restoreFullHp();
        $planet->addBuilding($sensor);

        self::assertTrue($player->hasSensorInSystem($sys, 1));
        self::assertTrue($player->hasSensorInSystem($sys, 2));
        // Range 3 > Sensor-Level 2 → false
        self::assertFalse($player->hasSensorInSystem($sys, 3));
    }

    public function test_sensor_in_other_system_returns_false(): void
    {
        $sysA = $this->makeSystem();
        $sysB = $this->makeSystem();
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->setSolarSystem($sysA);
        $player->claimPlanet($planet);

        $sensor = new Building(BuildingId::generate(), BuildingType::SENSOR_ARRAY, 5);
        $sensor->restoreFullHp();
        $planet->addBuilding($sensor);

        self::assertFalse($player->hasSensorInSystem($sysB, 1));
    }

    public function test_destroyed_sensor_excluded(): void
    {
        $sys = $this->makeSystem();
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->setSolarSystem($sys);
        $player->claimPlanet($planet);

        $sensor = new Building(BuildingId::generate(), BuildingType::SENSOR_ARRAY, 2);
        $sensor->restoreFullHp();
        $sensor->takeDamage($sensor->computeMaxHp());
        $planet->addBuilding($sensor);

        self::assertFalse($player->hasSensorInSystem($sys, 1));
    }

    private function makeSystem(): SolarSystem
    {
        return new SolarSystem(SolarSystemId::generate(), 'TestSys');
    }
}
