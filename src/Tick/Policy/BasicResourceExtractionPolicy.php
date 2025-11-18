<?php

namespace App\Tick\Policy;

use App\Building\Model\Building;
use App\Building\Service\ResourceBuildingMap;
use App\Resource\Model\ResourceDeposit;

readonly class BasicResourceExtractionPolicy
{
    public function __construct(private ResourceBuildingMap $resourceBuildingMap)
    {
    }

    /**
     * @param iterable<Building> $buildings
     */
    public function canExtract(ResourceDeposit $deposit, iterable $buildings): bool
    {
        if ($deposit->getAmount() <= 0) {
            return false;
        }

        $allowedBuildingTypes = $this->resourceBuildingMap->getBuildingsForResource($deposit->getResourceType());
        foreach ($buildings as $building) {
            if (in_array($building->getType(), $allowedBuildingTypes, true) && $building->getLevel() > 0) {
                return true;
            }
        }

        return false;
    }
}
