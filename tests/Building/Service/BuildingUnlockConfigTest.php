<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\BuildingUnlockConfig;
use App\Building\ValueObject\BuildingType;
use PHPUnit\Framework\TestCase;

final class BuildingUnlockConfigTest extends TestCase
{
    public function test_tier_0_buildings_have_no_lock(): void
    {
        // T-177: WAREHOUSE konsolidiert die 6 T-061 Storage-Buildings als Tier-0
        $config = new BuildingUnlockConfig();

        self::assertNull($config->requiredResearch(BuildingType::IRON_MINE));
        self::assertNull($config->requiredResearch(BuildingType::HUB));
        self::assertNull($config->requiredResearch(BuildingType::RESEARCH_LAB));
        self::assertNull($config->requiredResearch(BuildingType::WAREHOUSE));
        self::assertNull($config->requiredResearch(BuildingType::WATER_RECLAIMER));
    }

    public function test_basic_mining_unlocks(): void
    {
        // T-177: IRON_STORAGE / COAL_STORAGE gelöscht (durch WAREHOUSE ersetzt).
        $config = new BuildingUnlockConfig();
        foreach ([BuildingType::COAL_MINE, BuildingType::COPPER_MINE] as $bt) {
            self::assertSame(['slug' => 'basic_mining', 'level' => 1], $config->requiredResearch($bt), $bt->value);
        }
    }

    public function test_metallurgy_unlocks(): void
    {
        // T-177: IRON_BAR_STORAGE gelöscht (durch WAREHOUSE ersetzt).
        $config = new BuildingUnlockConfig();
        self::assertSame(['slug' => 'metallurgy', 'level' => 1], $config->requiredResearch(BuildingType::IRON_SMELTER));
    }

    public function test_advanced_mining_unlocks_tier2_mines(): void
    {
        $config = new BuildingUnlockConfig();
        foreach ([BuildingType::SILICON_MINE, BuildingType::ALUMINUM_MINE, BuildingType::TITANIUM_MINE, BuildingType::URANIUM_MINE] as $bt) {
            self::assertSame(['slug' => 'advanced_mining', 'level' => 1], $config->requiredResearch($bt), $bt->value);
        }
    }

    public function test_shipbuilding_unlocks_shipyard(): void
    {
        $config = new BuildingUnlockConfig();
        self::assertSame(['slug' => 'shipbuilding', 'level' => 1], $config->requiredResearch(BuildingType::SHIPYARD));
    }

    public function test_astronomy_unlocks_telescope_and_probe_lab(): void
    {
        $config = new BuildingUnlockConfig();
        self::assertSame(['slug' => 'astronomy', 'level' => 1], $config->requiredResearch(BuildingType::TELESCOPE));
        self::assertSame(['slug' => 'astronomy', 'level' => 1], $config->requiredResearch(BuildingType::PROBE_LAB));
    }

    public function test_recycling_unlocks_recycling_plant(): void
    {
        $config = new BuildingUnlockConfig();
        self::assertSame(['slug' => 'recycling', 'level' => 1], $config->requiredResearch(BuildingType::RECYCLING_PLANT));
    }
}
