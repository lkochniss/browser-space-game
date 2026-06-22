<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-070: CULTURAL_CENTER boostet Mining + Refinement um +2%/Level, capped +20%.
 */
final class CulturalCenterMultiplierTest extends IntegrationTestCase
{
    public function test_no_center_returns_baseline(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertSame(1.0, $planet->getCulturalCenterMultiplier());
    }

    public function test_l1_gives_2_percent(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::CULTURAL_CENTER, 1));

        self::assertEqualsWithDelta(1.02, $planet->getCulturalCenterMultiplier(), 0.0001);
    }

    public function test_l10_caps_at_20_percent(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::CULTURAL_CENTER, 10));

        // 0.02 × 10 = 0.20 (exakt am Cap)
        self::assertEqualsWithDelta(1.20, $planet->getCulturalCenterMultiplier(), 0.0001);
    }

    public function test_l50_clamped_at_20_percent(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::CULTURAL_CENTER, 50));

        // Soft-Cap auf +20% (würde sonst 1.0 + 1.0 = 2.0)
        self::assertEqualsWithDelta(1.20, $planet->getCulturalCenterMultiplier(), 0.0001);
    }

    public function test_mining_multiplier_stacks_with_planet_type_bonus(): void
    {
        // TERRAN (neutral mining bonus), Cultural-Center L5 → ×1.10 auf IRON_ORE
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::CULTURAL_CENTER, 5));

        $mining = $planet->getEffectiveMiningMultiplier(ResourceType::IRON_ORE);
        self::assertEqualsWithDelta(1.10, $mining, 0.0001, 'TERRAN (×1.0) × Cultural L5 (+10%) = 1.10');
    }
}
