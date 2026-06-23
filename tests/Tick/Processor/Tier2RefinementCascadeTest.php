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

/**
 * T-067: Tier-2 Refinement-Recipes + Snapshot-Single-Step-pro-Tick.
 *
 * Pro Tick wird EIN Refinement-Schritt verarbeitet — Cascade
 * (Iron-Bar→Steel→Hull-Plate im selben Tick) ist verhindert über
 * `refinedSnapshot`.
 */
final class Tier2RefinementCascadeTest extends TestCase
{
    private RefinementProductionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new RefinementProductionProcessor(new RefinementConfig());
    }

    public function test_aluminum_refinery_produces_bar(): void
    {
        // L1 ALUMINUM_REFINERY: 1× ALUMINUM_BAR = 2 ALUMINUM_ORE + 1 COAL
        $planet = $this->makePlanet();
        $planet->addResource(Resource::generateWithAmount(ResourceType::ALUMINUM_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::ALUMINUM_BAR));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::ALUMINUM_REFINERY, 1));

        $this->processor->process($planet);

        self::assertSame(98, $planet->getResource(ResourceType::ALUMINUM_ORE)->getAmount());
        self::assertSame(99, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::ALUMINUM_BAR)->getAmount());
    }

    public function test_steel_smelter_2_iron_bar_plus_1_coal_yields_1_steel(): void
    {
        // L1 STEEL_SMELTER: 1 STEEL aus 2 IRON_BAR + 1 COAL (per output unit)
        $planet = $this->makePlanet();
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 10));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 10));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::STEEL));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::STEEL_SMELTER, 1));

        $this->processor->process($planet);

        self::assertSame(8, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::STEEL)->getAmount());
    }

    public function test_cascade_blocked_in_single_tick(): void
    {
        // SNAPSHOT-CHECK: IRON_SMELTER + STEEL_SMELTER auf demselben Planet.
        // Pre-Tick: 100 IRON_ORE, 100 COAL, 0 IRON_BAR. STEEL_SMELTER soll
        // NICHT die in diesem Tick produzierten IRON_BARs nutzen.
        $planet = $this->makePlanet();
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_BAR));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::STEEL));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::STEEL_SMELTER, 1));

        $this->processor->process($planet);

        // IRON_SMELTER produziert 1 IRON_BAR
        self::assertSame(1, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
        // STEEL_SMELTER sieht den frischen IRON_BAR NICHT (Snapshot war 0) →
        // keine STEEL-Produktion in diesem Tick
        self::assertSame(0, $planet->getResource(ResourceType::STEEL)->getAmount());
    }

    public function test_cascade_works_over_two_ticks(): void
    {
        // Tick 1 produziert IRON_BAR, Tick 2 dann STEEL.
        $planet = $this->makePlanet();
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_BAR));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::STEEL));
        // L3 IRON_SMELTER → 3 IRON_BAR/Tick; L1 STEEL_SMELTER braucht 2 IRON_BAR (per Output)
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 3));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::STEEL_SMELTER, 1));

        $this->processor->process($planet);   // Tick 1
        $this->processor->process($planet);   // Tick 2

        // Tick 1: IRON_BAR 0→3 (von IRON_SMELTER), STEEL 0→0 (snapshot war 0)
        // Tick 2: STEEL_SMELTER sieht IRON_BAR snapshot=3, verbraucht 2 → STEEL=1.
        //          IRON_SMELTER produziert +3 IRON_BAR. IRON_BAR live = 3+3-2 = 4.
        self::assertSame(4, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::STEEL)->getAmount());
    }

    public function test_shield_module_needs_chip_and_tritium(): void
    {
        $planet = $this->makePlanet();
        $planet->addResource(Resource::generateWithAmount(ResourceType::CHIP, 10));
        $planet->addResource(Resource::generateWithAmount(ResourceType::TRITIUM_ORE, 10));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::SHIELD_MODULE));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIELD_ASSEMBLER, 1));

        $this->processor->process($planet);

        // 3 CHIP + 1 TRITIUM_ORE → 1 SHIELD_MODULE
        self::assertSame(7, $planet->getResource(ResourceType::CHIP)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::TRITIUM_ORE)->getAmount());
        self::assertSame(1, $planet->getResource(ResourceType::SHIELD_MODULE)->getAmount());
    }

    private function makePlanet(): Planet
    {
        return Planet::generatePlanet(PlanetId::generate());
    }
}
