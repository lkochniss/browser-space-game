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

/**
 * T-094c: HQ-Level erhöht parallel-Build-Queue-Slot-Cap (+1 pro 5 Lvl, max 8).
 */
final class PlanetBuildQueueCapTest extends TestCase
{
    public function test_no_hq_returns_base_cap_3(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        self::assertSame(3, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    public function test_hq_l1_no_bonus_yet(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hq = $this->makeReadyBuilding(BuildingType::HQ, 1);
        $planet->addBuilding($hq);

        self::assertSame(3, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    public function test_hq_l5_adds_one_slot(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hq = $this->makeReadyBuilding(BuildingType::HQ, 5);
        $planet->addBuilding($hq);

        self::assertSame(4, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    public function test_hq_l10_adds_two_slots(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hq = $this->makeReadyBuilding(BuildingType::HQ, 10);
        $planet->addBuilding($hq);

        self::assertSame(5, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    public function test_hq_l50_capped_at_8(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hq = $this->makeReadyBuilding(BuildingType::HQ, 50);
        $planet->addBuilding($hq);

        // base 3 + L50/5 = 13 → capped 8
        self::assertSame(8, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    public function test_unfinished_hq_no_bonus(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hq = new Building(BuildingId::generate(), BuildingType::HQ, 10);
        $hq->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($hq);

        self::assertSame(3, $planet->getEffectiveBuildQueueCap(new DateTimeImmutable()));
    }

    private function makeReadyBuilding(BuildingType $type, int $level): Building
    {
        $b = new Building(BuildingId::generate(), $type, $level);
        $b->setFinishedAt(new DateTimeImmutable('-1 minute'));

        return $b;
    }
}
