<?php

declare(strict_types=1);

namespace App\Tests\Ship\Model;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ShipTest extends TestCase
{
    public function test_new_ship_defaults(): void
    {
        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::GENERIC,
            populationAssigned: 20,
        );

        self::assertSame(ShipType::GENERIC, $ship->getType());
        self::assertSame(20, $ship->getPopulationAssigned());
        self::assertSame(0, $ship->getSupplyWater());
        self::assertSame(0, $ship->getSupplyFood());
        self::assertSame(0, $ship->getSupplyOxygen());
        self::assertSame(Ship::DEFAULT_SUPPLY_CAPACITY, $ship->getSupplyCapacity());
        self::assertNull($ship->getPlanet());
        self::assertFalse($ship->isDocked());
        self::assertNull($ship->getFinishedAt());
        self::assertTrue($ship->isReady());
    }

    public function test_dock_attaches_planet(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setPlanet($planet);

        self::assertTrue($ship->isDocked());
        self::assertSame($planet, $ship->getPlanet());
    }

    public function test_set_supplies_clamps_to_capacity(): void
    {
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setSupplies(100, 100, 100);

        self::assertSame(Ship::DEFAULT_SUPPLY_CAPACITY, $ship->getSupplyWater());
        self::assertSame(Ship::DEFAULT_SUPPLY_CAPACITY, $ship->getSupplyFood());
        self::assertSame(Ship::DEFAULT_SUPPLY_CAPACITY, $ship->getSupplyOxygen());
    }

    public function test_set_supplies_clamps_to_zero(): void
    {
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setSupplies(-10, -10, -10);

        self::assertSame(0, $ship->getSupplyWater());
        self::assertSame(0, $ship->getSupplyFood());
        self::assertSame(0, $ship->getSupplyOxygen());
    }

    public function test_isReady_with_finishedAt_in_past(): void
    {
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        self::assertTrue($ship->isReady(new DateTimeImmutable()));
    }

    public function test_isReady_with_finishedAt_in_future(): void
    {
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setFinishedAt(new DateTimeImmutable('+1 hour'));

        self::assertFalse($ship->isReady(new DateTimeImmutable()));
    }

    public function test_isReady_without_now_is_false_when_finishedAt_set(): void
    {
        $ship = new Ship(ShipId::generate(), ShipType::GENERIC, 20);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        self::assertFalse($ship->isReady(null));
    }
}
