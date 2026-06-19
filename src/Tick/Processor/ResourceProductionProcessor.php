<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Planet\Model\Planet;
use App\Resource\Service\ResourceProductionConfig;
use App\Tick\Interface\TickProcessorInterface;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use DateTimeImmutable;

readonly class ResourceProductionProcessor implements TickProcessorInterface
{
    public function __construct(
        private BasicResourceExtractionPolicy $policy,
        private ResourceBuildingMap $resourceBuildingMap,
        private ResourceProductionConfig $resourceProductionConfig,
        private ResourceProductionHelper $resourceProductionHelper,
    ) {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        foreach ($planet->getResourceDeposits() as $deposit) {
            if (!$this->policy->canExtract($deposit, $planet->getBuildings())) {
                continue;
            }

            $resourceType = $deposit->getResourceType();

            $desired = 0.0;
            $buildings = $this->resourceProductionHelper->getBuildingsForResourceOnPlanet($planet, $resourceType);
            $typeMulti = $planet->getEffectiveMiningMultiplier($resourceType); // T-063

            foreach ($buildings as $building) {
                // T-062: Building wirkt nur wenn ready
                if (!$building->isReady($now)) {
                    continue;
                }
                $baseValue = $this->resourceProductionConfig->getBaseProduction($resourceType);
                $multiplier = $this->resourceBuildingMap->getMultiplier($resourceType, $building->getType());
                $desired += $baseValue * $building->getLevel() * $multiplier * $typeMulti;
            }

            if ($desired <= 0) {
                continue;
            }

            $resource = $planet->getResource($resourceType);

            // Storage-cap stop (T-061): production pauses when storage full.
            $cap = $planet->getStorageCapacity($resourceType);
            $capRoom = max(0, $cap - $resource->getAmount());

            // Clamp by deposit availability AND by storage room.
            $extracted = (int) min($desired, $deposit->getAmount(), $capRoom);

            if ($extracted <= 0) {
                continue;
            }

            $deposit->setAmount($deposit->getAmount() - $extracted);
            $resource->setAmount($resource->getAmount() + $extracted);
        }
    }
}
