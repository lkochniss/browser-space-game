<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\Exception\BuildingNotFoundException;
use App\Building\Exception\BuildingNotInProgressException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-094b: Bricht einen laufenden Build/Upgrade ab.
 *
 * Refund-Strategie (Decision):
 * - 50% der Resources zurück (floor)
 * - 100% Pop wird auf Planet released
 *
 * Initial-Build (Building Level 1, gerade entstanden):
 * - Building wird gelöscht (em->remove)
 *
 * Upgrade (Building Level >= 2, gerade upgegradet):
 * - Level wird um 1 reduziert (zurück zum vorherigen)
 * - finishedAt = null → ist sofort wieder ready
 */
readonly class CancelBuildCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private BuildingCostConfig $costConfig,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlanetId $planetId, BuildingId $buildingId): ?Building
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
        if ($building->isReady($now)) {
            throw new BuildingNotInProgressException($buildingId);
        }

        // Cost-Calc: für Initial-Build (level 1) = getCost(type, 0).
        // Für Upgrade (level N+1) = getCost(type, N) = getCost(type, level-1).
        $level = $building->getLevel();
        $paidLevel = $level - 1;
        $cost = $this->costConfig->getCost($building->getType(), $paidLevel);

        // Refund
        foreach ($cost->iterateResources() as [$resourceType, $amount]) {
            $refund = (int) floor($amount * 0.5);
            if ($refund <= 0) {
                continue;
            }
            $resource = $planet->ensureResource($resourceType);
            $resource->setAmount($resource->getAmount() + $refund);
        }
        // Pop voll zurück
        if ($cost->populationCost > 0) {
            $planet->getPopulation()->release($cost->populationCost);
        }

        // Mutation
        if ($level === 1) {
            // Initial-Build → komplett entfernen
            $planet->getBuildings()->removeElement($building);
            $this->em->remove($building);
            $building = null;
        } else {
            // Upgrade-Cancel → Level um 1 zurück, sofort ready
            $building->setLevel($level - 1);
            $building->setFinishedAt(null);
            $planet->recalculatePopulationCap($now);
        }

        $this->em->flush();

        return $building;
    }

    private function findBuilding(Planet $planet, BuildingId $buildingId): ?Building
    {
        foreach ($planet->getBuildings() as $b) {
            if ($b->getId()->equals($buildingId)) {
                return $b;
            }
        }

        return null;
    }
}
