<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\Exception\BuildingLockedException;
use App\Building\Exception\BuildQueueFullException;
use App\Building\Exception\BuildingAlreadyExistsException;
use App\Building\Exception\InsufficientPopulationException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Exception\PlanetSlotsFullException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingCost;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;
use App\Resource\ValueObject\ResourceType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

readonly class BuildBuildingCommandService
{
    /**
     * T-094 Foundation: hardcoded Parallel-Slot-Cap pro Planet.
     * Folge-Tickets erhöhen via Hub-Upgrade / Logistics-Forschung.
     */
    public const MAX_CONCURRENT_BUILDS = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private BuildingCostConfig $costConfig,
        private BuildingDurationConfig $durationConfig,
        private ClockInterface $clock,
        private BuildingUnlockConfig $unlockConfig,
        private PlayerResearchRepository $playerResearchRepository,
        private ConstructionSpeedResearchConfig $constructionSpeedResearch,
    ) {
    }

    /**
     * T-170: Public Helper für Demo-CLI / UI — checkt ob ein Player ein
     * Building bauen darf (Research-Gate). Wirft KEINE Exception.
     */
    public function isUnlockedFor(Player $player, BuildingType $type): bool
    {
        $req = $this->unlockConfig->requiredResearch($type);
        if ($req === null) {
            return true;
        }
        $level = $this->playerResearchRepository
            ->findOneByPlayerAndSlug($player, $req['slug'])
            ?->getLevel() ?? 0;

        return $level >= $req['level'];
    }

    public function __invoke(PlanetId $planetId, BuildingType $type): Building
    {
        $planet = $this->planetRepository->find($planetId);
        if ($planet === null) {
            throw new PlanetNotFoundException($planetId);
        }

        $this->checkUnlock($planet, $type);

        // T-171 Uniqueness-Check
        if ($type->isUnique() && $planet->hasBuildingOfType($type)) {
            throw new BuildingAlreadyExistsException($type);
        }

        $now = $this->clock->now();

        // T-171 + T-172 Slot-Cap-Check (effektiver Cap inkl. HQ-Bonus)
        $needed = $type->getSlotSize();
        $used = $planet->getBuildingSlotsUsed();
        $cap = $planet->getEffectiveBuildingSlotCap($now);
        if ($used + $needed > $cap) {
            throw new PlanetSlotsFullException($used, $cap, $needed);
        }

        $active = $planet->countActiveBuildJobs($now);
        if ($active >= self::MAX_CONCURRENT_BUILDS) {
            throw new BuildQueueFullException($active, self::MAX_CONCURRENT_BUILDS);
        }

        $cost = $this->costConfig->getCost($type);

        $this->checkResources($planet, $cost);
        $this->checkPopulation($planet, $cost);

        $this->debitResources($planet, $cost);
        $planet->getPopulation()->assign($cost->populationCost);

        $rawDuration = $this->durationConfig->getDurationSeconds($type, currentLevel: 0);
        // T-063: PlanetType × Size Construction-Speed-Bonus
        $speedMulti = $planet->getEffectiveConstructionSpeedMultiplier($type);
        // T-064: Forschungs-Bonus stackt multiplikativ
        $speedMulti *= $this->constructionSpeedResearch->getMultiplier($planet->getPlayer());
        // T-064b → T-172 Rename: Lokales Construction-Yard-Building stackt multiplikativ
        $speedMulti *= $planet->getConstructionYardSpeedMultiplier($now);
        $duration = (int) max(1, round($rawDuration / $speedMulti));

        $building = Building::createNewBuilding($type);
        $building->setFinishedAt($now->add(new DateInterval(sprintf('PT%dS', $duration))));

        $planet->addBuilding($building, $now);

        $this->em->flush();

        return $building;
    }

    private function checkUnlock(Planet $planet, BuildingType $type): void
    {
        $req = $this->unlockConfig->requiredResearch($type);
        if ($req === null) {
            return;
        }
        $owner = $planet->getPlayer();
        if ($owner === null) {
            // Foundation: unowned Planet ist Demo/Test-Edge — kein Lock-Check (passiert
            // nur via Direct-Service-Call ohne ClaimStartPlanet-Bootstrap).
            return;
        }
        $level = $this->playerResearchRepository
            ->findOneByPlayerAndSlug($owner, $req['slug'])
            ?->getLevel() ?? 0;
        if ($level < $req['level']) {
            throw new BuildingLockedException($type, $req['slug'], $req['level']);
        }
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
