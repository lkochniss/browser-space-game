<?php

declare(strict_types=1);

namespace App\Tests\Building\ValueObject;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class BuildingTypeTest extends TestCase
{
    public function test_hub_contributes_50_per_level(): void
    {
        self::assertSame(50, BuildingType::HUB->getPopulationCapBonusPerLevel());
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

    public function test_shipyard_exists_and_contributes_neither_pop_cap_nor_storage(): void
    {
        self::assertSame('shipyard', BuildingType::SHIPYARD->value);
        self::assertSame(0, BuildingType::SHIPYARD->getPopulationCapBonusPerLevel());

        foreach (ResourceType::cases() as $resource) {
            self::assertSame(
                0,
                BuildingType::SHIPYARD->getStorageContribution($resource),
                $resource->value,
            );
        }
    }

    public function test_probe_lab_exists_and_contributes_neither_pop_cap_nor_storage(): void
    {
        self::assertSame('probe_lab', BuildingType::PROBE_LAB->value);
        self::assertSame(0, BuildingType::PROBE_LAB->getPopulationCapBonusPerLevel());

        foreach (ResourceType::cases() as $resource) {
            self::assertSame(
                0,
                BuildingType::PROBE_LAB->getStorageContribution($resource),
                $resource->value,
            );
        }
    }
}
