<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\Service\RenewableProductionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Processor\RenewableProductionProcessor;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class RenewableProductionProcessorTest extends TestCase
{
    public function test_no_op_without_producers(): void
    {
        $planet = $this->makePlanet(initialWater: 100);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        self::assertSame(100, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_water_reclaimer_l1_produces_10_water(): void
    {
        $planet = $this->makePlanet(initialWater: 0, waterReclaimerLevel: 1);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        self::assertSame(10, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_water_reclaimer_l3_produces_30_water(): void
    {
        $planet = $this->makePlanet(initialWater: 0, waterReclaimerLevel: 3);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        self::assertSame(30, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_unfinished_building_skipped(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 0));
        $b = new Building(BuildingId::generate(), BuildingType::WATER_RECLAIMER, 1);
        $b->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($b);

        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        self::assertSame(0, $planet->getResource(ResourceType::WATER)->getAmount(), 'unfertiges Building produziert nichts');
    }

    public function test_storage_cap_clamps_production(): void
    {
        // T-177 Volume: base 5000 m³, WATER_RECLAIMER trägt 0 m³ (Producer).
        // WATER multi=1.0. WATER=4995 (m³) → frei 5 m³ → 5 WATER addable.
        $planet = $this->makePlanet(initialWater: 4995, waterReclaimerLevel: 5);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        // 5 WATER addable bei 5 m³ frei → 4995 + 5 = 5000
        self::assertSame(5000, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_agri_dome_produces_food(): void
    {
        $planet = $this->makePlanet(initialFood: 0, agriDomeLevel: 2);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        // L2 × 6 = 12 FOOD
        self::assertSame(12, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    public function test_atmospheric_processor_produces_oxygen(): void
    {
        $planet = $this->makePlanet(initialOxygen: 0, atmosphericProcessorLevel: 1);
        $processor = new RenewableProductionProcessor(new RenewableProductionConfig());
        $processor->process($planet, new DateTimeImmutable('now'));

        self::assertSame(6, $planet->getResource(ResourceType::OXYGEN)->getAmount());
    }

    private function makePlanet(
        int $initialWater = 0,
        int $initialFood = 0,
        int $initialOxygen = 0,
        int $waterReclaimerLevel = 0,
        int $agriDomeLevel = 0,
        int $atmosphericProcessorLevel = 0,
    ): Planet {
        $planet = Planet::generatePlanet(PlanetId::generate());
        if ($initialWater > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, $initialWater));
        } else {
            $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 0));
        }
        if ($initialFood >= 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::FOOD, $initialFood));
        }
        if ($initialOxygen >= 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::OXYGEN, $initialOxygen));
        }
        $now = new DateTimeImmutable('-1 minute');

        // T-065 Power: HUB L1000 ready, deckt Renewable-Building-Consumption ab.
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1000);
        $hub->setFinishedAt($now);
        $planet->addBuilding($hub);

        if ($waterReclaimerLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::WATER_RECLAIMER, $waterReclaimerLevel);
            $b->setFinishedAt($now);
            $planet->addBuilding($b);
        }
        if ($agriDomeLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::AGRI_DOME, $agriDomeLevel);
            $b->setFinishedAt($now);
            $planet->addBuilding($b);
        }
        if ($atmosphericProcessorLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::ATMOSPHERIC_PROCESSOR, $atmosphericProcessorLevel);
            $b->setFinishedAt($now);
            $planet->addBuilding($b);
        }

        return $planet;
    }
}
