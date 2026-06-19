<?php

declare(strict_types=1);

namespace App\Building\ValueObject;

use App\Resource\ValueObject\ResourceType;

enum BuildingType: string
{
    case IRON_MINE = 'iron_mine';
    case COAL_MINE = 'coal_mine';
    case COPPER_MINE = 'copper_mine';
    case SILICON_MINE = 'silicon_mine';
    case ALUMINUM_MINE = 'aluminum_mine';
    case TITANIUM_MINE = 'titanium_mine';
    case URANIUM_MINE = 'uranium_mine';

    case HUB = 'hub';

    case IRON_SMELTER = 'iron_smelter';

    case SHIPYARD = 'shipyard';

    case PROBE_LAB = 'probe_lab';

    // T-021: Recycling-Plant. Verarbeitet DEBRIS_* Cargo zu zufälligen FINITE/REFINED-Outputs.
    case RECYCLING_PLANT = 'recycling_plant';

    case IRON_STORAGE = 'iron_storage';
    case COAL_STORAGE = 'coal_storage';
    case IRON_BAR_STORAGE = 'iron_bar_storage';
    case WATER_TANK = 'water_tank';
    case FOOD_SILO = 'food_silo';
    case OXYGEN_STORAGE = 'oxygen_storage';

    public function getPopulationCapBonusPerLevel(): int
    {
        return match ($this) {
            self::HUB => 50,
            default => 0,
        };
    }

    /**
     * Storage capacity contribution per Building-Level for a given resource (T-061).
     *
     * - Mining-Mines bringen kleinen Buffer für ihre eigene Resource
     * - IRON_SMELTER puffert IRON_BAR
     * - HUB bringt natural Renewable-Storage (Lebensraum-Boost)
     * - Dedizierte Storage-Buildings bringen viel
     */
    public function getStorageContribution(ResourceType $resource): int
    {
        $contributions = match ($this) {
            self::IRON_MINE => [ResourceType::IRON_ORE->value => 100],
            self::COAL_MINE => [ResourceType::COAL->value => 100],
            self::COPPER_MINE => [ResourceType::COPPER_ORE->value => 100],
            self::SILICON_MINE => [ResourceType::SILICON->value => 100],
            self::ALUMINUM_MINE => [ResourceType::ALUMINUM_ORE->value => 100],
            self::TITANIUM_MINE => [ResourceType::TITANIUM_ORE->value => 100],
            self::URANIUM_MINE => [ResourceType::URANIUM_ORE->value => 100],
            self::IRON_SMELTER => [ResourceType::IRON_BAR->value => 100],
            self::SHIPYARD => [],
            self::PROBE_LAB => [],
            self::RECYCLING_PLANT => [],
            self::HUB => [
                ResourceType::WATER->value => 200,
                ResourceType::FOOD->value => 200,
                ResourceType::OXYGEN->value => 200,
            ],
            self::IRON_STORAGE => [ResourceType::IRON_ORE->value => 1000],
            self::COAL_STORAGE => [ResourceType::COAL->value => 1000],
            self::IRON_BAR_STORAGE => [ResourceType::IRON_BAR->value => 1000],
            self::WATER_TANK => [ResourceType::WATER->value => 2000],
            self::FOOD_SILO => [ResourceType::FOOD->value => 2000],
            self::OXYGEN_STORAGE => [ResourceType::OXYGEN->value => 2000],
        };

        return $contributions[$resource->value] ?? 0;
    }
}
