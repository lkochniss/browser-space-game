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
 * T-065 Energy-System Foundation. Live-berechnete Power-Bilanz pro Planet.
 */
final class PlanetPowerBalanceTest extends TestCase
{
    public function test_empty_planet_has_zero_balance_and_ratio_one(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertSame(0, $planet->getPowerProduced());
        self::assertSame(0, $planet->getPowerConsumed());
        self::assertSame(0, $planet->getPowerBalance());
        self::assertSame(1.0, $planet->getPowerThrottleRatio());
    }

    public function test_hub_l1_produces_75_power(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));

        self::assertSame(75, $planet->getPowerProduced());
        // HUB konsumiert 1 × 1 = 1
        self::assertSame(1, $planet->getPowerConsumed());
        self::assertSame(74, $planet->getPowerBalance());
        self::assertSame(1.0, $planet->getPowerThrottleRatio());
    }

    public function test_iron_mine_alone_zero_produced_full_throttle(): void
    {
        // Mine L1 konsumiert 3, kein Producer → ratio = 0
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1));

        self::assertSame(0, $planet->getPowerProduced());
        self::assertSame(3, $planet->getPowerConsumed());
        self::assertSame(0.0, $planet->getPowerThrottleRatio());
    }

    public function test_partial_undersupply_ratio(): void
    {
        // HUB L1 = 75 produced, Shipyard L10 = 150 consumed + HUB self 1 = 151
        // Ratio = 75 / 151 ≈ 0.497
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 10));

        self::assertSame(75, $planet->getPowerProduced());
        self::assertSame(151, $planet->getPowerConsumed());
        self::assertEqualsWithDelta(75 / 151, $planet->getPowerThrottleRatio(), 0.0001);
    }

    public function test_oversupply_caps_ratio_to_one(): void
    {
        // HUB L10 = 300 produced, HUB self 10 = 10 consumed → ratio = 1.0 (cap)
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 10));

        self::assertSame(300, $planet->getPowerProduced());
        self::assertSame(10, $planet->getPowerConsumed());
        self::assertSame(1.0, $planet->getPowerThrottleRatio());
    }

    public function test_unfinished_building_ignored_in_balance(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $finished = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $finished->setFinishedAt((new \DateTimeImmutable())->modify('-1 hour'));
        $planet->addBuilding($finished);

        $inProgress = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 5);
        $inProgress->setFinishedAt((new \DateTimeImmutable())->modify('+1 hour'));
        $planet->addBuilding($inProgress);

        $now = new \DateTimeImmutable();
        // Only HUB ready → 75 produced / 1 consumed (HUB self)
        self::assertSame(75, $planet->getPowerProduced($now));
        self::assertSame(1, $planet->getPowerConsumed($now));
    }
}
