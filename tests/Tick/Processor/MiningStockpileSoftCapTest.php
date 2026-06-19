<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\SoftCapConfig;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use App\Tick\Processor\ResourceProductionProcessor;
use PHPUnit\Framework\TestCase;

final class MiningStockpileSoftCapTest extends TestCase
{
    public function test_mining_unaffected_below_stockpile_threshold(): void
    {
        // Stockpile 50k < 100k Threshold → kein Soft-Cap
        $planet = $this->makePlanet(currentStockpile: 50_000);
        $processor = $this->makeProcessor();
        $processor->process($planet);

        $produced = $planet->getResource(ResourceType::IRON_ORE)->getAmount() - 50_000;
        self::assertGreaterThan(0, $produced, 'mining produces normally below threshold');
    }

    public function test_mining_throttled_above_stockpile_threshold(): void
    {
        // Stockpile 600k → mining-multiplier sollte 0.5 (Min-Clamp) sein
        $planet = $this->makePlanet(currentStockpile: 600_000);
        $processor = $this->makeProcessor();

        $producedHigh = $this->measureProduction($planet);

        // Vergleiche mit niedrigerem Stockpile
        $planetLow = $this->makePlanet(currentStockpile: 50_000);
        $producedLow = $this->measureProduction($planetLow);

        self::assertLessThan($producedLow, $producedHigh, 'high stockpile throttles mining');
    }

    private function measureProduction(Planet $planet): int
    {
        $before = $planet->getResource(ResourceType::IRON_ORE)->getAmount();
        $processor = $this->makeProcessor();
        $processor->process($planet);

        return $planet->getResource(ResourceType::IRON_ORE)->getAmount() - $before;
    }

    private function makePlanet(int $currentStockpile): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $currentStockpile));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 1_000_000));

        // Storage-Cap > Stockpile damit cap-Stop nicht greift
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_STORAGE, 1000));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1));

        return $planet;
    }

    private function makeProcessor(): ResourceProductionProcessor
    {
        $map = new ResourceBuildingMap();

        return new ResourceProductionProcessor(
            new BasicResourceExtractionPolicy($map),
            $map,
            new ResourceProductionConfig(),
            new ResourceProductionHelper($map),
            new SoftCapConfig(),
        );
    }
}
