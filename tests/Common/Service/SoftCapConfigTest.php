<?php

declare(strict_types=1);

namespace App\Tests\Common\Service;

use App\Common\Service\SoftCapConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SoftCapConfigTest extends TestCase
{
    /**
     * @return array<string, array{int, float}>
     */
    public static function popGrowthProvider(): array
    {
        return [
            'below_threshold'     => [500_000, 1.0],
            'at_threshold'        => [1_000_000, 1.0],
            'just_above'          => [1_001_000, 0.999999],
            'mid_range'           => [500_000_000, 0.501],
            'extreme_clamps_min'  => [10_000_000_000, 0.1],
        ];
    }

    #[DataProvider('popGrowthProvider')]
    public function test_pop_growth_multiplier(int $pop, float $expected): void
    {
        $cfg = new SoftCapConfig();
        self::assertEqualsWithDelta($expected, $cfg->popGrowthMultiplier($pop), 0.01);
    }

    /**
     * @return array<string, array{int, float}>
     */
    public static function buildingCostProvider(): array
    {
        return [
            'level_0'              => [0, 1.0],
            'level_19_below_thr'   => [19, 1.0],
            'level_20_at_thr'      => [20, 1.0],
            'level_21_one_step'    => [21, 1.05],
            'level_30_ten_steps'   => [30, 1.05 ** 10],
        ];
    }

    #[DataProvider('buildingCostProvider')]
    public function test_building_cost_multiplier(int $level, float $expected): void
    {
        $cfg = new SoftCapConfig();
        self::assertEqualsWithDelta($expected, $cfg->buildingCostMultiplier($level), 0.0001);
    }

    /**
     * @return array<string, array{int, float}>
     */
    public static function miningProvider(): array
    {
        return [
            'below_threshold'     => [50_000, 1.0],
            'at_threshold'        => [100_000, 1.0],
            'just_above'          => [110_000, 0.99],
            'mid_range'           => [600_000, 0.5],
            'extreme_clamps_min'  => [10_000_000, 0.5],
        ];
    }

    #[DataProvider('miningProvider')]
    public function test_mining_multiplier(int $stockpile, float $expected): void
    {
        $cfg = new SoftCapConfig();
        self::assertEqualsWithDelta($expected, $cfg->miningMultiplier($stockpile), 0.01);
    }
}
