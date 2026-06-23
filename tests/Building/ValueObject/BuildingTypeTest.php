<?php

declare(strict_types=1);

namespace App\Tests\Building\ValueObject;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class BuildingTypeTest extends TestCase
{
    public function test_hub_contributes_100_per_level(): void
    {
        // T-172: HUB ist jetzt multi-instance Wohnsiedlung mit 100 Pop-Cap/Level
        self::assertSame(100, BuildingType::HUB->getPopulationCapBonusPerLevel());
    }

    public function test_hq_contributes_25_per_level(): void
    {
        // T-172: HQ ist zentrale Verwaltung mit 25 Pop-Cap-Foundation/Level
        self::assertSame(25, BuildingType::HQ->getPopulationCapBonusPerLevel());
    }

    public function test_mines_have_no_pop_cap_bonus(): void
    {
        $mines = [
            BuildingType::IRON_MINE,
            BuildingType::COAL_MINE,
            BuildingType::COPPER_MINE,
            BuildingType::SILICON_MINE,
            BuildingType::ALUMINUM_MINE,
            BuildingType::TITANIUM_MINE,
            BuildingType::URANIUM_MINE,
        ];

        foreach ($mines as $mine) {
            self::assertSame(0, $mine->getPopulationCapBonusPerLevel(), $mine->value);
        }
    }

    public function test_shipyard_exists_and_contributes_neither_pop_cap_nor_volume(): void
    {
        // T-177: getStorageContribution(R) entfernt — neue API getVolumeContribution()
        self::assertSame('shipyard', BuildingType::SHIPYARD->value);
        self::assertSame(0, BuildingType::SHIPYARD->getPopulationCapBonusPerLevel());
        self::assertSame(0, BuildingType::SHIPYARD->getVolumeContribution());
    }

    public function test_probe_lab_exists_and_contributes_neither_pop_cap_nor_volume(): void
    {
        self::assertSame('probe_lab', BuildingType::PROBE_LAB->value);
        self::assertSame(0, BuildingType::PROBE_LAB->getPopulationCapBonusPerLevel());
        self::assertSame(0, BuildingType::PROBE_LAB->getVolumeContribution());
    }

    public function test_warehouse_volume_contribution(): void
    {
        // T-177: WAREHOUSE +500 m³/Lvl (Hauptquelle Generic-Volume-Storage)
        self::assertSame(500, BuildingType::WAREHOUSE->getVolumeContribution());
    }

    public function test_hq_volume_contribution(): void
    {
        // T-177: HQ +25 m³/Lvl (Verwaltungs-Buffer)
        self::assertSame(25, BuildingType::HQ->getVolumeContribution());
    }
}
