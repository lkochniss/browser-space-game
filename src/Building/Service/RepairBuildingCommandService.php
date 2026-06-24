<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\Exception\BuildingNotDamagedException;
use App\Building\Exception\BuildingNotFoundException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Exception\RepairCooldownActiveException;
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

/**
 * T-068 Repair-Mechanik: Player zahlt 30% Initial-Build-Cost auf current-Level,
 * Cooldown 24h, Building geht zurück auf 100% HP.
 */
readonly class RepairBuildingCommandService
{
    public const REPAIR_COST_FACTOR = 0.30;
    public const REPAIR_COOLDOWN_SECONDS = 24 * 3600;

    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private BuildingCostConfig $costConfig,
        private ClockInterface $clock,
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
            throw new BuildingNotFoundException($buildingId);
        }

        if ($building->getCurrentHp() >= $building->computeMaxHp()) {
            throw new BuildingNotDamagedException($buildingId);
        }

        $now = $this->clock->now();
        $last = $building->getLastRepairAt();
        if ($last !== null) {
            $elapsed = $now->getTimestamp() - $last->getTimestamp();
            $remaining = self::REPAIR_COOLDOWN_SECONDS - $elapsed;
            if ($remaining > 0) {
                throw new RepairCooldownActiveException($buildingId, $remaining);
            }
        }

        $repairCost = $this->computeRepairCost($building);
        $this->checkResources($planet, $repairCost);
        $this->debitResources($planet, $repairCost);

        $building->restoreFullHp();
        $building->setLastRepairAt($now);

        $this->em->flush();

        return $building;
    }

    private function computeRepairCost(Building $building): BuildingCost
    {
        // currentLevel argument to getCost is "previous level for upgrade-pricing".
        // 0 = initial-build = base cost. T-068 nutzt base × 30%.
        $base = $this->costConfig->getCost($building->getType(), 0);
        $scaledResources = [];
        foreach ($base->iterateResources() as [$type, $amount]) {
            $scaled = (int) ceil($amount * self::REPAIR_COST_FACTOR);
            if ($scaled > 0) {
                $scaledResources[$type->value] = $scaled;
            }
        }

        // T-068: 30% Pop-Re-Assign skipt — Repair zieht keine zusätzliche Pop
        // (Building bleibt mit assigned-Pop existieren).
        return new BuildingCost(resources: $scaledResources, populationCost: 0);
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

    private function findBuilding(Planet $planet, BuildingId $id): ?Building
    {
        foreach ($planet->getBuildings() as $b) {
            if ($b->getId()->equals($id)) {
                return $b;
            }
        }

        return null;
    }
}
