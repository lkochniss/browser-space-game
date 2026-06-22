<?php

declare(strict_types=1);

namespace App\Tests\Ship\ValueObject;

use App\Ship\ValueObject\PropulsionType;
use PHPUnit\Framework\TestCase;

final class PropulsionTypeTest extends TestCase
{
    public function test_hydrogen_is_foundation_no_research(): void
    {
        self::assertNull(PropulsionType::HYDROGEN->getRequiredResearchSlug());
        self::assertSame(1.0, PropulsionType::HYDROGEN->getSpeedMultiplier());
        self::assertSame(0, PropulsionType::HYDROGEN->getMaxSystemRange());
        self::assertFalse(PropulsionType::HYDROGEN->isFtl());
    }

    public function test_standard_propulsions_no_ftl(): void
    {
        self::assertSame(0, PropulsionType::ION->getMaxSystemRange());
        self::assertSame(0, PropulsionType::FUSION->getMaxSystemRange());
        self::assertSame(0, PropulsionType::ANTIMATTER->getMaxSystemRange());
    }

    public function test_speed_multiplier_monotonic_within_standard_chain(): void
    {
        // Standard-Antriebe: HYDROGEN < ION < FUSION < ANTIMATTER
        self::assertGreaterThan(
            PropulsionType::HYDROGEN->getSpeedMultiplier(),
            PropulsionType::ION->getSpeedMultiplier(),
        );
        self::assertGreaterThan(
            PropulsionType::ION->getSpeedMultiplier(),
            PropulsionType::FUSION->getSpeedMultiplier(),
        );
        self::assertGreaterThan(
            PropulsionType::FUSION->getSpeedMultiplier(),
            PropulsionType::ANTIMATTER->getSpeedMultiplier(),
        );
    }

    public function test_ftl_chain_has_increasing_range(): void
    {
        self::assertSame(1, PropulsionType::HYPERDRIVE->getMaxSystemRange());
        self::assertSame(3, PropulsionType::WARP->getMaxSystemRange());
        self::assertSame(10, PropulsionType::JUMPDRIVE->getMaxSystemRange());
        self::assertTrue(PropulsionType::HYPERDRIVE->isFtl());
        self::assertTrue(PropulsionType::WARP->isFtl());
        self::assertTrue(PropulsionType::JUMPDRIVE->isFtl());
    }

    public function test_research_slugs_match_propulsion_tree(): void
    {
        self::assertSame('propulsion_ion', PropulsionType::ION->getRequiredResearchSlug());
        self::assertSame('propulsion_fusion', PropulsionType::FUSION->getRequiredResearchSlug());
        self::assertSame('propulsion_antimatter', PropulsionType::ANTIMATTER->getRequiredResearchSlug());
        self::assertSame('ftl_hyperdrive', PropulsionType::HYPERDRIVE->getRequiredResearchSlug());
        self::assertSame('ftl_warp', PropulsionType::WARP->getRequiredResearchSlug());
        self::assertSame('ftl_jumpdrive', PropulsionType::JUMPDRIVE->getRequiredResearchSlug());
    }
}
