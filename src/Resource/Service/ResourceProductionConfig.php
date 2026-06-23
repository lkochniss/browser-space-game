<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Resource\ValueObject\ResourceType;

class ResourceProductionConfig
{
    private array $baseProduction = [
        ResourceType::IRON_ORE->value => 10.0,
        ResourceType::COAL->value => 15.0,
        ResourceType::COPPER_ORE->value => 8.0,
        ResourceType::SILICON->value => 6.0,
        ResourceType::ALUMINUM_ORE->value => 8.0,
        ResourceType::TITANIUM_ORE->value => 4.0,
        ResourceType::URANIUM_ORE->value => 2.0,

        // T-067 Tier-2 Mines (selten, mittlere Rate)
        ResourceType::PLASTIC_RESIN->value => 5.0,
        ResourceType::TRITIUM_ORE->value => 3.0,

        ResourceType::WATER->value => 5.0,
        ResourceType::FOOD->value => 3.0,
        ResourceType::OXYGEN->value => 0.0,
    ];

    public function getBaseProduction(ResourceType $resource): float
    {
        return $this->baseProduction[$resource->value] ?? 0.0;
    }
}
