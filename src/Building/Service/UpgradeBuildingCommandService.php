<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\Exception\BuildingNotFoundException;
use App\Building\Exception\BuildQueueFullException;
use App\Building\Exception\InsufficientPopulationException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingCost;
use App\Building\ValueObject\BuildingId;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpgradeBuildingCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private BuildingCostConfig $costConfig,
        private BuildingDurationConfig $durationConfig,
        private ClockInterface $clock,
        private ConstructionSpeedResearchConfig $constructionSpeedResearch,
    ) {
    }

    public function __invoke(PlanetId $planetId, BuildingId $buildingId): Building
    {
        $planet = $this->planetRepository->find($planetId);
        if ($planet === null) {
            throw new PlanetNotFoundException($planetId);
        }

        $building = $this->findBuilding($planet, $buildingId);
        if ($building === null) {
            throw new BuildingNotFoundException($planetId, $buildingId);
        }

        $now = $this->clock->now();
        $active = $planet->countActiveBuildJobs($now);
        if ($active >= BuildBuildingCommandService::MAX_CONCURRENT_BUILDS) {
            throw new BuildQueueFullException($active, BuildBuildingCommandService::MAX_CONCURRENT_BUILDS);
        }

        $currentLevel = $building->getLevel();
        $cost = $this->costConfig->getCost($building->getType(), $currentLevel);
        $rawDuration = $this->durationConfig->getDurationSeconds($building->getType(), $currentLevel);
        // T-063: PlanetType × Size Construction-Speed-Bonus
        $speedMulti = $planet->getEffectiveConstructionSpeedMultiplier($building->getType());
        // T-064: Forschungs-Bonus stackt multiplikativ
        $speedMulti *= $this->constructionSpeedResearch->getMultiplier($planet->getPlayer());
        // T-064b → T-172 Rename: Lokales Construction-Yard-Building stackt multiplikativ
        $speedMulti *= $planet->getConstructionYardSpeedMultiplier($now);
        $duration = (int) max(1, round($rawDuration / $speedMulti));

        $this->checkResources($planet, $cost);
        $this->checkPopulation($planet, $cost);

        $this->debitResources($planet, $cost);
        $planet->getPopulation()->assign($cost->populationCost);

        $building->setLevel($currentLevel + 1);
        $building->setFinishedAt($now->add(new DateInterval(sprintf('PT%dS', $duration))));

        $planet->recalculatePopulationCap($now);

        $this->em->flush();

        return $building;
    }

    private function findBuilding(Planet $planet, BuildingId $buildingId): ?Building
    {
        foreach ($planet->getBuildings() as $building) {
            if ($building->getId()->equals($buildingId)) {
                return $building;
            }
        }

        return null;
    }

    private function checkResources(Planet $planet, BuildingCost $cost): void
    {
        foreach ($cost->iterateResources() as [$resourceType, $required]) {
            $available = $this->getResourceAmount($planet, $resourceType);
            if ($available < $required) {
                throw new InsufficientResourcesException($resourceType, $required, $available);
            }
        }
    }

    private function checkPopulation(Planet $planet, BuildingCost $cost): void
    {
        $free = $planet->getPopulation()->getFree();
        if ($free < $cost->populationCost) {
            throw new InsufficientPopulationException($cost->populationCost, $free);
        }
    }

    private function debitResources(Planet $planet, BuildingCost $cost): void
    {
        foreach ($cost->iterateResources() as [$resourceType, $amount]) {
            $resource = $planet->getResource($resourceType);
            $resource->setAmount($resource->getAmount() - $amount);
        }
    }

    private function getResourceAmount(Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource->getAmount();
            }
        }

        return 0;
    }
}
