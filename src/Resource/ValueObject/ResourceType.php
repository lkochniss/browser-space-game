<?php

declare(strict_types=1);

namespace App\Resource\ValueObject;

enum ResourceType: string
{
    case IRON_ORE = 'iron_ore';
    case COAL = 'coal';
    case COPPER_ORE = 'copper_ore';
    case SILICON = 'silicon';
    case ALUMINUM_ORE = 'aluminum_ore';
    case TITANIUM_ORE = 'titanium_ore';
    case URANIUM_ORE = 'uranium_ore';

    case WATER = 'water';
    case FOOD = 'food';
    case OXYGEN = 'oxygen';

    case IRON_BAR = 'iron_bar';

    // T-021: Trümmer-Cargo. Werden via Salvage aus DebrisFields geholt und
    // via RecyclingProcessor in zufällige FINITE/REFINED-Outputs konvertiert.
    case DEBRIS_LOW = 'debris_low';
    case DEBRIS_MEDIUM = 'debris_medium';
    case DEBRIS_HIGH = 'debris_high';

    public function getCategory(): ResourceCategory
    {
        return match ($this) {
            self::WATER, self::FOOD, self::OXYGEN => ResourceCategory::RENEWABLE,
            self::IRON_BAR => ResourceCategory::REFINED,
            self::DEBRIS_LOW, self::DEBRIS_MEDIUM, self::DEBRIS_HIGH => ResourceCategory::DEBRIS,
            default => ResourceCategory::FINITE,
        };
    }
}
