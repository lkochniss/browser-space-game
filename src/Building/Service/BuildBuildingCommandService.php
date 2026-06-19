<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\Exception\InsufficientPopulationException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingCost;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

readonly class BuildBuildingCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private BuildingCostConfig $costConfig,
        private BuildingDurationConfig $durationConfig,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlanetId $planetId, BuildingType $type): Building
    {
        $planet = $this->planetRepository->find($planetId);
        if ($planet === null) {
            throw new PlanetNotFoundException($planetId);
        }

        $cost = $this->costConfig->getCost($type);

        $this->checkResources($planet, $cost);
        $this->checkPopulation($planet, $cost);

        $this->debitResources($planet, $cost);
        $planet->getPopulation()->assign($cost->populationCost);

        $now = $this->clock->now();
        $rawDuration = $this->durationConfig->getDurationSeconds($type, currentLevel: 0);
        // T-063: PlanetType × Size Construction-Speed-Bonus reduziert Duration
        $speedMulti = $planet->getEffectiveConstructionSpeedMultiplier($type);
        $duration = (int) max(1, round($rawDuration / $speedMulti));

        $building = Building::createNewBuilding($type);
        $building->setFinishedAt($now->add(new DateInterval(sprintf('PT%dS', $duration))));

        $planet->addBuilding($building, $now);

        $this->em->flush();

        return $building;
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
