<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\Service\RefinementConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Processor\RefinementProductionProcessor;
use PHPUnit\Framework\TestCase;

final class RefinementProductionProcessorTest extends TestCase
{
    private RefinementProductionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new RefinementProductionProcessor(new RefinementConfig());
    }

    public function test_l1_smelter_produces_one_bar_per_tick(): void
    {
        $planet = $this->makePlanet(smelterLevel: 1, iron: 100, coal: 100);

        $this->processor->process($planet);

        self::assertSame(98, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(99, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_l3_smelter_produces_three_bars(): void
    {
        $planet = $this->makePlanet(smelterLevel: 3, iron: 100, coal: 100);

        $this->processor->process($planet);

        // 3 bars × (2 iron + 1 coal)
        self::assertSame(94, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(97, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(3, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_iron_limited_production(): void
    {
        // L3 wants 6 iron + 3 coal, only 4 iron available → max 2 bars (4 iron + 2 coal)
        $planet = $this->makePlanet(smelterLevel: 3, iron: 4, coal: 100);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(98, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(2, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_coal_limited_production(): void
    {
        // L3 wants 6 iron + 3 coal, only 1 coal available → max 1 bar (2 iron + 1 coal)
        $planet = $this->makePlanet(smelterLevel: 3, iron: 100, coal: 1);

        $this->processor->process($planet);

        self::assertSame(98, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_no_inputs_no_production(): void
    {
        $planet = $this->makePlanet(smelterLevel: 1, iron: 0, coal: 0, prefillBars: false);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::COAL)->getAmount());
        // No production → IRON_BAR resource never created (no zero-output side effect)
        self::assertNull($this->findResource($planet, ResourceType::IRON_BAR));
    }

    public function test_no_smelter_is_noop(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));

        $this->processor->process($planet);

        self::assertSame(100, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::COAL)->getAmount());
    }

    public function test_iron_bar_resource_auto_created_on_first_production(): void
    {
        // Ensure IRON_BAR isn't pre-seeded
        $planet = $this->makePlanet(smelterLevel: 1, iron: 100, coal: 100, prefillBars: false);
        self::assertNull($this->findResource($planet, ResourceType::IRON_BAR));

        $this->processor->process($planet);

        self::assertNotNull($this->findResource($planet, ResourceType::IRON_BAR));
        self::assertSame(1, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_multiple_smelters_stack_output(): void
    {
        $planet = $this->makePlanet(smelterLevel: 1, iron: 100, coal: 100);
        // add a second L2 smelter
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 2));

        $this->processor->process($planet);

        // L1 produces 1, L2 produces 2 → 3 bars total (6 iron + 3 coal)
        self::assertSame(94, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(97, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(3, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_storage_cap_stops_refinement_when_full(): void
    {
        // T-177 Volume: base 5000 + IRON_SMELTER L1 (50) = 5050 m³.
        // 10 iron (20m³) + 10 coal (18m³) + 3348 iron_bar (5022m³) = 5060 m³ → über cap.
        $planet = $this->makePlanet(smelterLevel: 1, iron: 10, coal: 10);
        $planet->getResource(ResourceType::IRON_BAR)->setAmount(3348);

        $this->processor->process($planet);

        // Kein IRON_BAR addable → keine Production, keine Inputs konsumiert
        self::assertSame(10, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(10, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(3348, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_storage_cap_partially_clamps_refinement(): void
    {
        // T-177 Volume: base 5000 + IRON_SMELTER L3 (150) = 5150 m³.
        // 10 iron (20m³) + 10 coal (18m³) + 3406 iron_bar (5109m³) = 5147 m³ used.
        // Free = 3 m³ → max 2 bars (3/1.5=2). L3 wants 3 bars → 2 produced.
        $planet = $this->makePlanet(smelterLevel: 3, iron: 10, coal: 10);
        $planet->getResource(ResourceType::IRON_BAR)->setAmount(3406);

        $this->processor->process($planet);

        // 2 Bars produziert: 4 iron + 2 coal verbraucht
        self::assertSame(6, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(8, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(3408, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_in_progress_smelter_does_not_refine(): void
    {
        // T-062: Smelter mit finishedAt in Zukunft → keine Refinement
        $planet = $this->makePlanet(smelterLevel: 1, iron: 100, coal: 100);
        $smelter = $planet->getBuildings()->first();
        $smelter->setFinishedAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->processor->process($planet, new \DateTimeImmutable());

        self::assertSame(100, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    private function makePlanet(int $smelterLevel, int $iron, int $coal, bool $prefillBars = true): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $iron));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, $coal));
        if ($prefillBars) {
            $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_BAR));
        }

        // T-065 Power: HUB L1000 deckt Refinery-Consumption ab.
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1000));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, $smelterLevel));

        return $planet;
    }

    private function findResource(Planet $planet, ResourceType $type): ?Resource
    {
        foreach ($planet->getResources() as $r) {
            if ($r->getType() === $type) {
                return $r;
            }
        }

        return null;
    }
}
