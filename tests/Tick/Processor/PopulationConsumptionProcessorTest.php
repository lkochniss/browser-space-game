<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\Service\PopulationConsumptionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Processor\PopulationConsumptionProcessor;
use PHPUnit\Framework\TestCase;

final class PopulationConsumptionProcessorTest extends TestCase
{
    private PopulationConsumptionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new PopulationConsumptionProcessor(new PopulationConsumptionConfig());
    }

    public function test_pop_zero_is_noop(): void
    {
        $planet = $this->makePlanet(popTotal: 0, water: 100, food: 100);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getPopulation()->getTotal());
        self::assertSame(100, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    public function test_consume_water_and_food_when_supplied(): void
    {
        $planet = $this->makePlanet(popTotal: 50, water: 100, food: 100);

        $this->processor->process($planet);

        // 50 * 0.1 = 5 each
        self::assertSame(95, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(95, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    public function test_logistic_growth_at_50_of_100(): void
    {
        $planet = $this->makePlanet(popTotal: 50, water: 100, food: 100);

        $this->processor->process($planet);

        // logistic: r=0.1, P=50, K=100 → delta = 0.1 * 50 * 0.5 = 2.5 → round = 3
        self::assertSame(53, $planet->getPopulation()->getTotal());
    }

    public function test_no_growth_at_cap(): void
    {
        $planet = $this->makePlanet(popTotal: 100, water: 100, food: 100, cap: 100);

        $this->processor->process($planet);

        self::assertSame(100, $planet->getPopulation()->getTotal());
    }

    public function test_water_shortage_kills_pop(): void
    {
        // Pop=50 needs 5 water. Provide 0 → shortage 5 → 5/0.1=50 deaths
        $planet = $this->makePlanet(popTotal: 50, water: 0, food: 100);

        $this->processor->process($planet);

        // 50 deaths but pop only 50 → killed all
        self::assertSame(0, $planet->getPopulation()->getTotal());
        self::assertSame(0, $planet->getResource(ResourceType::WATER)->getAmount());
        // food still consumed up to available
        self::assertSame(95, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    public function test_food_shortage_kills_pop(): void
    {
        // Pop=50 needs 5 food. Provide 2 → shortage 3 → 3/0.1=30 deaths
        $planet = $this->makePlanet(popTotal: 50, water: 100, food: 2);

        $this->processor->process($planet);

        self::assertSame(20, $planet->getPopulation()->getTotal());
        self::assertSame(0, $planet->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(95, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_kill_takes_from_free_first(): void
    {
        // Pop=50 (assigned=20, free=30). Food shortage → kill 30 (only free).
        // Provide 2 food → shortage 3 → 30 deaths → all free die, assigned untouched
        $planet = $this->makePlanet(popTotal: 50, water: 100, food: 2, assigned: 20);

        $this->processor->process($planet);

        self::assertSame(20, $planet->getPopulation()->getTotal());
        self::assertSame(20, $planet->getPopulation()->getAssigned());
        self::assertSame(0, $planet->getPopulation()->getFree());
    }

    public function test_severe_shortage_kills_into_assigned(): void
    {
        // Pop=10 (assigned=8, free=2). Water=0 → shortage 1 → 10 deaths
        $planet = $this->makePlanet(popTotal: 10, water: 0, food: 100, assigned: 8);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getPopulation()->getTotal());
        self::assertSame(0, $planet->getPopulation()->getAssigned());
    }

    public function test_no_growth_during_shortage(): void
    {
        $planet = $this->makePlanet(popTotal: 50, water: 0, food: 100);

        $this->processor->process($planet);

        // Shortage path: kill, no growth — only deaths happen
        self::assertSame(0, $planet->getPopulation()->getTotal());
    }

    public function test_desert_planet_consumes_50_percent_more_water_and_food(): void
    {
        $planet = $this->makePlanet(
            popTotal: 50,
            water: 100,
            food: 100,
            type: \App\Planet\ValueObject\PlanetType::DESERT,
        );

        $this->processor->process($planet);

        // 50 * 0.1 * 1.5 = 7.5 → ceil = 8
        self::assertSame(92, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(92, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    public function test_ocean_planet_uses_half_water(): void
    {
        $planet = $this->makePlanet(
            popTotal: 50,
            water: 100,
            food: 100,
            type: \App\Planet\ValueObject\PlanetType::OCEAN,
        );

        $this->processor->process($planet);

        // Water: 50 * 0.1 * 0.5 = 2.5 → ceil = 3 → 100-3 = 97
        // Food: 50 * 0.1 * 1.0 = 5
        self::assertSame(97, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(95, $planet->getResource(ResourceType::FOOD)->getAmount());
    }

    private function makePlanet(
        int $popTotal,
        int $water,
        int $food,
        int $cap = 100,
        int $assigned = 0,
        \App\Planet\ValueObject\PlanetType $type = \App\Planet\ValueObject\PlanetType::TERRAN,
    ): Planet {
        $planet = Planet::generatePlanet(PlanetId::generate(), $type);
        $planet->getPopulation()->setCap($cap);
        $planet->getPopulation()->grow($popTotal);
        if ($assigned > 0) {
            $planet->getPopulation()->assign($assigned);
        }

        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, $water));
        $planet->addResource(Resource::generateWithAmount(ResourceType::FOOD, $food));

        return $planet;
    }
}
