<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\BuildingCostConfig;
use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class BuildingCostSoftCapTest extends TestCase
{
    public function test_below_level_20_unaffected_by_softcap(): void
    {
        $config = new BuildingCostConfig();
        $costAtL19 = $config->getCost(BuildingType::IRON_MINE, currentLevel: 19);
        // Bei lvl 19: nur 2^19 multiplier, kein softcap
        $expected = (int) ceil(50 * (2 ** 19));
        self::assertSame($expected, $costAtL19->resources[ResourceType::IRON_ORE->value]);
    }

    public function test_at_level_20_unaffected_by_softcap(): void
    {
        $config = new BuildingCostConfig();
        $costAtL20 = $config->getCost(BuildingType::IRON_MINE, currentLevel: 20);
        // Bei lvl 20: 2^20 × 1.0 (1.05^0)
        $expected = (int) ceil(50 * (2 ** 20));
        self::assertSame($expected, $costAtL20->resources[ResourceType::IRON_ORE->value]);
    }

    public function test_above_level_20_softcap_kicks_in(): void
    {
        $config = new BuildingCostConfig();
        $costAtL21 = $config->getCost(BuildingType::IRON_MINE, currentLevel: 21);
        // Bei lvl 21: 2^21 × 1.05 (1.05^1)
        $expected = (int) ceil(50 * (2 ** 21) * 1.05);
        self::assertSame($expected, $costAtL21->resources[ResourceType::IRON_ORE->value]);
    }

    public function test_high_level_softcap_compounds(): void
    {
        $config = new BuildingCostConfig();
        $costAtL30 = $config->getCost(BuildingType::IRON_MINE, currentLevel: 30);
        // Bei lvl 30: 2^30 × 1.05^10 ≈ 2^30 × 1.629
        $expected = (int) ceil(50 * (2 ** 30) * (1.05 ** 10));
        self::assertSame($expected, $costAtL30->resources[ResourceType::IRON_ORE->value]);
    }
}
