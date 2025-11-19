<?php

namespace App\Building\Service;

use App\Building\Model\Building;
use App\Planet\Model\Planet;
use App\Resource\ValueObject\ResourceType;

class ResourceProductionHelper
{
    public function __construct(private ResourceBuildingMap $map)
    {
    }

    public function getBuildingsForResourceOnPlanet(Planet $planet, ResourceType $resourceType): array
    {
        $allowedBuildings = $this->map->getBuildingsForResource($resourceType);

        return array_filter(
            $planet->getBuildings()->toArray(),
            fn(Building $building) => in_array($building->getType()->value, $allowedBuildings, true)
        );
    }
}
