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

    public function getCategory(): ResourceCategory
    {
        return match ($this) {
            self::WATER, self::FOOD, self::OXYGEN => ResourceCategory::RENEWABLE,
            self::IRON_BAR => ResourceCategory::REFINED,
            default => ResourceCategory::FINITE,
        };
    }
}
