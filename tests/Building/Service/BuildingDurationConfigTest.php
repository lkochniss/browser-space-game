<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\BuildingDurationConfig;
use App\Building\ValueObject\BuildingType;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BuildingDurationConfigTest extends TestCase
{
    /**
     * @return array<string, array{BuildingType, int}>
     */
    public static function baseDurationProvider(): array
    {
        return [
            'iron_mine'      => [BuildingType::IRON_MINE,       300],
            'coal_mine'      => [BuildingType::COAL_MINE,       300],
            'uranium_mine'   => [BuildingType::URANIUM_MINE,    300],
            'hq'             => [BuildingType::HQ,              3600],
            'hub'            => [BuildingType::HUB,             900],
            'iron_smelter'   => [BuildingType::IRON_SMELTER,    1800],
            'shipyard'       => [BuildingType::SHIPYARD,        3600],
            'probe_lab'      => [BuildingType::PROBE_LAB,       1800],
            'iron_storage'   => [BuildingType::IRON_STORAGE,    900],
            'water_tank'     => [BuildingType::WATER_TANK,      900],
            'food_silo'      => [BuildingType::FOOD_SILO,       900],
        ];
    }

    #[DataProvider('baseDurationProvider')]
    public function test_initial_build_duration(BuildingType $type, int $expectedSeconds): void
    {
        $config = new BuildingDurationConfig();
        self::assertSame($expectedSeconds, $config->getDurationSeconds($type));
    }

    public function test_upgrade_l1_doubles_duration(): void
    {
        $config = new BuildingDurationConfig();
        // IRON_MINE base 300s, currentLevel=1 → 2× = 600s
        self::assertSame(600, $config->getDurationSeconds(BuildingType::IRON_MINE, currentLevel: 1));
    }

    public function test_upgrade_l5_uses_32x_multiplier(): void
    {
        $config = new BuildingDurationConfig();
        // T-172: HQ base 3600s × 32 = 115200s
        self::assertSame(115200, $config->getDurationSeconds(BuildingType::HQ, currentLevel: 5));
    }

    public function test_negative_level_throws(): void
    {
        $config = new BuildingDurationConfig();
        $this->expectException(LogicException::class);
        $config->getDurationSeconds(BuildingType::IRON_MINE, currentLevel: -1);
    }
}
