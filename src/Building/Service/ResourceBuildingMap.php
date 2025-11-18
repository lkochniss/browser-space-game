<?php

namespace App\Building\Service;

use App\Resource\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;

class ResourceBuildingMap
{
    /**
     * @var array<ResourceType, BuildingType[]>
     */
    private array $map;

    public function __construct()
    {
        $this->map = [
            ResourceType::IRON_ORE->value => [
                BuildingType::IRON_MINE->value => 1.0
            ],
        ];
    }

    public function getBuildingsForResource(ResourceType $resourceType): array
    {
        return $this->map[$resourceType->value] ?? [];
    }

    public function canProduce(BuildingType $buildingType, ResourceType $resourceType): bool
    {
        return in_array($buildingType, $this->getBuildingsForResource($resourceType), true);
    }

    public function getMultiplier(ResourceType $resource, BuildingType $building): float
    {
        return $this->map[$resource->value][$building->value] ?? 0.0;
    }
}
