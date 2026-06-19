<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;

class ResourceBuildingMap
{
    /**
     * @var array<string, array<string, float>>
     */
    private array $map;

    public function __construct()
    {
        $this->map = [
            ResourceType::IRON_ORE->value => [
                BuildingType::IRON_MINE->value => 1.0,
            ],
            ResourceType::COAL->value => [
                BuildingType::COAL_MINE->value => 1.0,
            ],
            ResourceType::COPPER_ORE->value => [
                BuildingType::COPPER_MINE->value => 1.0,
            ],
            ResourceType::SILICON->value => [
                BuildingType::SILICON_MINE->value => 1.0,
            ],
            ResourceType::ALUMINUM_ORE->value => [
                BuildingType::ALUMINUM_MINE->value => 1.0,
            ],
            ResourceType::TITANIUM_ORE->value => [
                BuildingType::TITANIUM_MINE->value => 1.0,
            ],
            ResourceType::URANIUM_ORE->value => [
                BuildingType::URANIUM_MINE->value => 1.0,
            ],
        ];
    }

    public function getBuildingsForResource(ResourceType $resource): array
    {
        $byResource = $this->map[$resource->value] ?? [];

        return array_keys($byResource);
    }

    public function canProduce(BuildingType $buildingType, ResourceType $resourceType): bool
    {
        return in_array($buildingType->value, $this->getBuildingsForResource($resourceType), true);
    }

    public function getMultiplier(ResourceType $resource, BuildingType $building): float
    {
        return $this->map[$resource->value][$building->value] ?? 0.0;
    }
}
