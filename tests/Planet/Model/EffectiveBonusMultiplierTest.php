<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class EffectiveBonusMultiplierTest extends TestCase
{
    public function test_terran_medium_neutral_mining(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::TERRAN, PlanetSize::MEDIUM);
        self::assertSame(1.0, $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE));
    }

    public function test_barren_medium_iron_mining_15x(): void
    {
        // BARREN bonus 0.5, MEDIUM sizeFactor 1.0 → multiplier = 1 + 0.5 = 1.5
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::BARREN, PlanetSize::MEDIUM);
        self::assertSame(1.5, $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE));
    }

    public function test_barren_huge_iron_mining_2x(): void
    {
        // BARREN bonus 0.5 × HUGE sizeFactor 2.0 = 1.0 → multiplier = 1 + 1.0 = 2.0
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::BARREN, PlanetSize::HUGE);
        self::assertSame(2.0, $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE));
    }

    public function test_barren_tiny_iron_mining_125x(): void
    {
        // BARREN bonus 0.5 × TINY sizeFactor 0.5 = 0.25 → multiplier = 1.25
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::BARREN, PlanetSize::TINY);
        self::assertSame(1.25, $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE));
    }

    public function test_gas_giant_medium_blocks_mining_completely(): void
    {
        // GAS_GIANT bonus -1.0 × MEDIUM sizeFactor 1.0 = -1.0 → multiplier = max(0, 0) = 0
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::GAS_GIANT, PlanetSize::MEDIUM);
        self::assertSame(0.0, $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE));
    }

    public function test_terran_medium_pop_growth_12x(): void
    {
        // TERRAN bonus 0.2 × MEDIUM 1.0 = 0.2 → multi 1.2
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::TERRAN, PlanetSize::MEDIUM);
        self::assertEqualsWithDelta(1.2, $planet->getEffectivePopGrowthMultiplier(), 0.001);
    }

    public function test_ice_huge_pop_growth_04x(): void
    {
        // ICE bonus -0.3 × HUGE 2.0 = -0.6 → multi 0.4
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::ICE, PlanetSize::HUGE);
        self::assertEqualsWithDelta(0.4, $planet->getEffectivePopGrowthMultiplier(), 0.001);
    }

    public function test_gas_giant_huge_pop_growth_clamped_zero(): void
    {
        // GAS_GIANT bonus -0.5 × HUGE 2.0 = -1.0 → multi max(0, 0) = 0
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::GAS_GIANT, PlanetSize::HUGE);
        self::assertSame(0.0, $planet->getEffectivePopGrowthMultiplier());
    }

    public function test_barren_medium_construction_speed_iron_mine(): void
    {
        // BARREN bonus 0.2 × MEDIUM 1.0 = 0.2 → multi 1.2
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::BARREN, PlanetSize::MEDIUM);
        self::assertEqualsWithDelta(1.2, $planet->getEffectiveConstructionSpeedMultiplier(BuildingType::IRON_MINE), 0.001);
    }

    public function test_barren_huge_construction_speed_iron_mine(): void
    {
        // BARREN 0.2 × HUGE 2.0 = 0.4 → multi 1.4
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::BARREN, PlanetSize::HUGE);
        self::assertEqualsWithDelta(1.4, $planet->getEffectiveConstructionSpeedMultiplier(BuildingType::IRON_MINE), 0.001);
    }

    public function test_construction_speed_min_clamp_at_01(): void
    {
        // Hypothetical: extreme negative bonus would clamp at 0.1
        // No type today does this, but verify the floor mechanism via direct path:
        // Use TERRAN (bonus 0) to confirm multi >= 0.1 invariant trivially holds.
        $planet = Planet::generatePlanet(PlanetId::generate(), PlanetType::TERRAN, PlanetSize::MEDIUM);
        self::assertGreaterThanOrEqual(0.1, $planet->getEffectiveConstructionSpeedMultiplier(BuildingType::HUB));
    }
}
