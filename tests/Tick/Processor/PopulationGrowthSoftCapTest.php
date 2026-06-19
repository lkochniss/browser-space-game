<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Common\Service\SoftCapConfig;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\Service\PopulationConsumptionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Processor\PopulationConsumptionProcessor;
use PHPUnit\Framework\TestCase;

final class PopulationGrowthSoftCapTest extends TestCase
{
    public function test_growth_unaffected_below_softcap_threshold(): void
    {
        // Pop 500k unter 1M-Threshold → kein Soft-Cap
        // Pop-Cap muss > total sein damit growth möglich
        $planet = $this->makePlanet(popTotal: 500_000, water: 1_000_000_000, food: 1_000_000_000, cap: 2_000_000);

        $processor = new PopulationConsumptionProcessor(
            new PopulationConsumptionConfig(),
            new SoftCapConfig(),
        );
        $processor->process($planet);

        // Wachstum sollte stattfinden ohne Drosselung
        self::assertGreaterThan(500_000, $planet->getPopulation()->getTotal());
    }

    public function test_growth_throttled_above_softcap_threshold(): void
    {
        // Pop 1.5M über Threshold; vergleiche zu hypothetisch ungedrossletem
        $planet1 = $this->makePlanet(popTotal: 1_500_000, water: 100_000_000_000, food: 100_000_000_000, cap: 10_000_000);
        $planet2 = $this->makePlanet(popTotal: 1_500_000, water: 100_000_000_000, food: 100_000_000_000, cap: 10_000_000);

        $processorWithSoftCap = new PopulationConsumptionProcessor(
            new PopulationConsumptionConfig(),
            new SoftCapConfig(),
        );
        $processorWithoutSoftCap = new PopulationConsumptionProcessor(
            new PopulationConsumptionConfig(),
            new class extends SoftCapConfig {
                public function popGrowthMultiplier(int $popTotal): float
                {
                    return 1.0;
                }
            },
        );

        $processorWithSoftCap->process($planet1);
        $processorWithoutSoftCap->process($planet2);

        // Mit Soft-Cap sollte das Wachstum kleiner oder gleich sein
        self::assertLessThanOrEqual(
            $planet2->getPopulation()->getTotal(),
            $planet1->getPopulation()->getTotal(),
        );
    }

    private function makePlanet(int $popTotal, int $water, int $food, int $cap): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->getPopulation()->setCap($cap);
        $planet->getPopulation()->grow($popTotal);
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, $water));
        $planet->addResource(Resource::generateWithAmount(ResourceType::FOOD, $food));

        return $planet;
    }
}
