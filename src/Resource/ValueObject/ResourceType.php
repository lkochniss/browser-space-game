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

    // T-067: 2 neue FINITE Erze als Tier-2-Inputs
    case PLASTIC_RESIN = 'plastic_resin';  // TROPICAL/OCEAN-Vorkommen, → COMPOSITE
    case TRITIUM_ORE = 'tritium_ore';      // VOLCANIC/ICE-Vorkommen, → SHIELD_MODULE

    case WATER = 'water';
    case FOOD = 'food';
    case OXYGEN = 'oxygen';

    case IRON_BAR = 'iron_bar';

    // T-067: 8 neue REFINED Tier-2-Erzeugnisse (Bars + Compounds)
    case ALUMINUM_BAR = 'aluminum_bar';
    case COPPER_BAR = 'copper_bar';
    case TITANIUM_BAR = 'titanium_bar';
    case STEEL = 'steel';
    case CHIP = 'chip';
    case COMPOSITE = 'composite';
    case HULL_PLATE = 'hull_plate';
    case SHIELD_MODULE = 'shield_module';

    // T-021: Trümmer-Cargo. Werden via Salvage aus DebrisFields geholt und
    // via RecyclingProcessor in zufällige FINITE/REFINED-Outputs konvertiert.
    case DEBRIS_LOW = 'debris_low';
    case DEBRIS_MEDIUM = 'debris_medium';
    case DEBRIS_HIGH = 'debris_high';

    public function getCategory(): ResourceCategory
    {
        return match ($this) {
            self::WATER, self::FOOD, self::OXYGEN => ResourceCategory::RENEWABLE,
            self::IRON_BAR,
            self::ALUMINUM_BAR,
            self::COPPER_BAR,
            self::TITANIUM_BAR,
            self::STEEL,
            self::CHIP,
            self::COMPOSITE,
            self::HULL_PLATE,
            self::SHIELD_MODULE => ResourceCategory::REFINED,
            self::DEBRIS_LOW, self::DEBRIS_MEDIUM, self::DEBRIS_HIGH => ResourceCategory::DEBRIS,
            default => ResourceCategory::FINITE,
        };
    }
}
