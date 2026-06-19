<?php

declare(strict_types=1);

namespace App\Tests\Planet\ValueObject;

use App\Building\ValueObject\BuildingType;
use App\Planet\ValueObject\PlanetType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class PlanetTypeBonusTest extends TestCase
{
    public function test_terran_baseline_zero(): void
    {
        self::assertSame(0.0, PlanetType::TERRAN->getMiningBonus(ResourceType::IRON_ORE));
        self::assertSame(0.0, PlanetType::TERRAN->getRefinementBonus(ResourceType::IRON_BAR));
        self::assertSame(0.2, PlanetType::TERRAN->getPopGrowthBonus()); // TERRAN ist habitabel-bevorzugt
        self::assertSame(0.0, PlanetType::TERRAN->getConstructionSpeedBonus(BuildingType::IRON_MINE));
    }

    public function test_barren_iron_copper_mining_boost(): void
    {
        self::assertSame(0.5, PlanetType::BARREN->getMiningBonus(ResourceType::IRON_ORE));
        self::assertSame(0.5, PlanetType::BARREN->getMiningBonus(ResourceType::COPPER_ORE));
        self::assertSame(0.0, PlanetType::BARREN->getMiningBonus(ResourceType::SILICON));
    }

    public function test_volcanic_uranium_strong_boost(): void
    {
        self::assertSame(1.0, PlanetType::VOLCANIC->getMiningBonus(ResourceType::URANIUM_ORE));
        self::assertSame(0.5, PlanetType::VOLCANIC->getMiningBonus(ResourceType::IRON_ORE));
    }

    public function test_desert_silicon_strong_titanium_moderate(): void
    {
        self::assertSame(1.0, PlanetType::DESERT->getMiningBonus(ResourceType::SILICON));
        self::assertSame(0.5, PlanetType::DESERT->getMiningBonus(ResourceType::TITANIUM_ORE));
    }

    public function test_gas_giant_blocks_all_mining(): void
    {
        self::assertSame(-1.0, PlanetType::GAS_GIANT->getMiningBonus(ResourceType::IRON_ORE));
        self::assertSame(-1.0, PlanetType::GAS_GIANT->getMiningBonus(ResourceType::URANIUM_ORE));
    }

    public function test_pop_growth_bonus_per_type(): void
    {
        self::assertSame(0.2,  PlanetType::TERRAN->getPopGrowthBonus());
        self::assertSame(0.1,  PlanetType::OCEAN->getPopGrowthBonus());
        self::assertSame(-0.1, PlanetType::BARREN->getPopGrowthBonus());
        self::assertSame(-0.1, PlanetType::VOLCANIC->getPopGrowthBonus());
        self::assertSame(-0.2, PlanetType::DESERT->getPopGrowthBonus());
        self::assertSame(-0.3, PlanetType::ICE->getPopGrowthBonus());
        self::assertSame(-0.5, PlanetType::GAS_GIANT->getPopGrowthBonus());
    }

    public function test_barren_construction_speed_bonus_only_for_mines(): void
    {
        self::assertSame(0.2, PlanetType::BARREN->getConstructionSpeedBonus(BuildingType::IRON_MINE));
        self::assertSame(0.2, PlanetType::BARREN->getConstructionSpeedBonus(BuildingType::URANIUM_MINE));
        self::assertSame(0.0, PlanetType::BARREN->getConstructionSpeedBonus(BuildingType::HUB));
        self::assertSame(0.0, PlanetType::BARREN->getConstructionSpeedBonus(BuildingType::IRON_SMELTER));
    }

    public function test_refinement_bonus_zero_today(): void
    {
        // Refinement-Bonus ist Tuning-Punkt — heute alle 0
        foreach (PlanetType::cases() as $type) {
            self::assertSame(0.0, $type->getRefinementBonus(ResourceType::IRON_BAR));
        }
    }
}
