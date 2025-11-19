<?php

namespace App\Tick\Processor;

use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Planet\Model\Planet;
use App\Resource\Service\ResourceProductionConfig;
use App\Tick\Interface\TickProcessorInterface;
use App\Tick\Policy\BasicResourceExtractionPolicy;

readonly class ResourceProductionProcessor implements TickProcessorInterface
{
    public function __construct(
        private BasicResourceExtractionPolicy $policy,
        private ResourceBuildingMap           $resourceBuildingMap,
        private ResourceProductionConfig      $resourceProductionConfig,
        private ResourceProductionHelper      $resourceProductionHelper,
    )
    {
    }

    public function process(Planet $planet): void
    {

        foreach ($planet->getResourceDeposits() as $deposit) {
            if (!$this->policy->canExtract($deposit, $planet->getBuildings())) {
                continue;
            }

            $amount = 0;

            $buildings = $this->resourceProductionHelper->getBuildingsForResourceOnPlanet($planet, $deposit->getResourceType());

            foreach ($buildings as $building) {
                $baseValue = $this->resourceProductionConfig->getBaseProduction($deposit->getResourceType());
                $multiplier = $this->resourceBuildingMap->getMultiplier($deposit->getResourceType(), $building->getType());
                $amount += $baseValue * ($building->getLevel() + 1) * $multiplier;
            }

            $deposit->setAmount($deposit->getAmount() - $amount);

            $resource = $planet->getResource($deposit->getResourceType());
            $resource->setAmount($resource->getAmount() + $amount);
        }
    }
}
