<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Research\Exception\AlreadyResearchingException;
use App\Research\Exception\InsufficientResearchResourcesException;
use App\Research\Exception\InvalidLabSelectionException;
use App\Research\Exception\LabLevelTooLowException;
use App\Research\Exception\MaxLevelReachedException;
use App\Research\Exception\PrerequisiteNotMetException;
use App\Research\Exception\ResearchLabMissingException;
use App\Research\Model\ActiveResearch;
use App\Research\Model\Prerequisite\PlayerResearchLookup;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-025 + T-025c Validation + Effect für Forschungs-Start.
 *
 * T-025c Multi-Lab Opt-In (ersetzt T-025b Auto-Aggregator):
 * Player wählt explizit einen **Primary-Lab-Planet** + optional **Booster-
 * Lab-Planeten**. Wenn keine Auswahl: Default = stärkster Lab des Players
 * als Primary, keine Booster (Single-Lab-Verhalten, kein Cost-Aufschlag).
 *
 * Validation-Reihenfolge:
 *  1. Player existiert
 *  2. Mindestens ein ready RESEARCH_LAB beim Player (über alle Planeten)
 *  3. Keine andere ActiveResearch
 *  4. Primary + Booster gehören Player, haben ready Labs, kein Overlap
 *  5. Node existiert
 *  6. Max-Level
 *  7. Prerequisites
 *  8. Resources (über alle Player-Planeten, FIFO)
 *
 * Effekt: Resources abziehen, ActiveResearch persistieren mit
 *   `finished_at = now + duration(effectiveLab)` und persistiertem
 *   primary/booster-Planet-IDs (Frozen-at-Start, D4).
 */
readonly class StartResearchCommandService implements PlayerResearchLookup
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepository,
        private PlayerResearchRepository $playerResearchRepository,
        private ActiveResearchRepository $activeResearchRepository,
        private ResearchTree $tree,
        private ResearchDurationConfig $durationConfig,
        private ClockInterface $clock,
    ) {
    }

    public function getPlayerResearchLevel(Player $player, string $nodeSlug): int
    {
        return $this->playerResearchRepository
            ->findOneByPlayerAndSlug($player, $nodeSlug)
            ?->getLevel() ?? 0;
    }

    /**
     * @param list<PlanetId> $boosterLabPlanetIds
     */
    public function __invoke(
        PlayerId $playerId,
        string $nodeSlug,
        ?PlanetId $primaryLabPlanetId = null,
        array $boosterLabPlanetIds = [],
    ): ActiveResearch {
        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new \RuntimeException(sprintf('Player %s nicht gefunden', $playerId));
        }

        $now = $this->clock->now();

        // 2. Lab-Gate: mindestens ein Ready-Lab muss existieren
        if (!$this->hasAnyReadyLab($player, $now)) {
            throw new ResearchLabMissingException();
        }

        // 3. Aktive Forschung?
        $active = $this->activeResearchRepository->findActiveForPlayer($player);
        if ($active !== null) {
            throw new AlreadyResearchingException($active->getNodeSlug());
        }

        // 4. Lab-Selection validieren + resolved instances holen
        [$primaryPlanet, $boosterPlanets] = $this->resolveLabSelection($player, $primaryLabPlanetId, $boosterLabPlanetIds, $now);

        // 5. Node existiert
        $node = $this->tree->get($nodeSlug);

        // 6. Max-Level
        $current = $this->playerResearchRepository->findOneByPlayerAndSlug($player, $nodeSlug);
        $currentLevel = $current?->getLevel() ?? 0;
        $targetLevel = $currentLevel + 1;
        if ($targetLevel > $node->maxLevel) {
            throw new MaxLevelReachedException($nodeSlug, $node->maxLevel);
        }

        // 7. Prerequisites (T-170 polymorph)
        foreach ($node->prerequisites as $prereq) {
            if (!$prereq->isMetBy($player, $now, $this)) {
                throw new PrerequisiteNotMetException($nodeSlug, $prereq);
            }
        }

        // 8. Effective-Lab + Cost
        $primaryLvl = $this->labLevel($primaryPlanet, $now);
        $boosterLvls = array_map(fn (Planet $p): int => $this->labLevel($p, $now), $boosterPlanets);
        $effectiveLab = $this->tree->computeEffectiveLabLevel($primaryLvl, $boosterLvls);

        // T-069: Lab-Tier-Gate — effective Lab muss requiredLabLevel erreichen
        if ($effectiveLab < (float) $node->requiredLabLevel) {
            throw new LabLevelTooLowException($nodeSlug, $node->requiredLabLevel, $effectiveLab);
        }

        $cost = $this->durationConfig->resourceCost($node, $targetLevel, $primaryLvl, $boosterLvls);

        $totals = $this->aggregatePlayerResources($player);
        foreach ($cost as $resourceVal => $needed) {
            $available = $totals[$resourceVal] ?? 0;
            if ($available < $needed) {
                throw new InsufficientResearchResourcesException($resourceVal, $needed, $available);
            }
        }

        // Effekt: Resources abziehen
        foreach ($cost as $resourceVal => $needed) {
            $this->deductFromPlayer($player, ResourceType::from($resourceVal), $needed);
        }

        // Persist
        $duration = $this->durationConfig->durationSeconds($node, $targetLevel, $effectiveLab);
        $finishedAt = $now->add(new \DateInterval(sprintf('PT%dS', $duration)));

        $boosterIds = array_map(fn (Planet $p): PlanetId => $p->getId(), $boosterPlanets);
        $entry = ActiveResearch::generate(
            $player,
            $nodeSlug,
            $targetLevel,
            $now,
            $finishedAt,
            $primaryPlanet->getId(),
            $boosterIds,
        );
        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    /**
     * T-025c: Convenience-Read-API für Demo-CLI + UIs.
     *
     * Liefert alle Player-Planeten mit ready RESEARCH_LAB inkl. Level.
     *
     * @return list<array{planet: Planet, labLevel: int}>
     */
    public function listReadyLabs(Player $player, \DateTimeImmutable $now): array
    {
        $result = [];
        foreach ($player->getPlanets() as $planet) {
            $lvl = $this->labLevel($planet, $now);
            if ($lvl > 0) {
                $result[] = ['planet' => $planet, 'labLevel' => $lvl];
            }
        }

        return $result;
    }

    private function hasAnyReadyLab(Player $player, \DateTimeImmutable $now): bool
    {
        foreach ($player->getPlanets() as $planet) {
            if ($this->labLevel($planet, $now) > 0) {
                return true;
            }
        }

        return false;
    }

    private function labLevel(Planet $planet, \DateTimeImmutable $now): int
    {
        $max = 0;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getType() !== BuildingType::RESEARCH_LAB) {
                continue;
            }
            if (!$b->isReady($now)) {
                continue;
            }
            if ($b->getLevel() > $max) {
                $max = $b->getLevel();
            }
        }

        return $max;
    }

    /**
     * @param list<PlanetId> $boosterLabPlanetIds
     *
     * @return array{0: Planet, 1: list<Planet>}
     */
    private function resolveLabSelection(
        Player $player,
        ?PlanetId $primaryLabPlanetId,
        array $boosterLabPlanetIds,
        \DateTimeImmutable $now,
    ): array {
        $primary = $primaryLabPlanetId !== null
            ? $this->lookupOwnedPlanet($player, $primaryLabPlanetId, primary: true)
            : $this->pickStrongestReadyLabPlanet($player, $now);

        if ($this->labLevel($primary, $now) <= 0) {
            throw InvalidLabSelectionException::primaryLabNotReady($primary->getId());
        }

        $boosterPlanets = [];
        $seenIds = [(string) $primary->getId() => true];
        foreach ($boosterLabPlanetIds as $boosterId) {
            $key = (string) $boosterId;
            if (isset($seenIds[$key])) {
                if ($key === (string) $primary->getId()) {
                    throw InvalidLabSelectionException::primaryInBoosters($boosterId);
                }
                throw InvalidLabSelectionException::duplicateBooster($boosterId);
            }
            $seenIds[$key] = true;

            $planet = $this->lookupOwnedPlanet($player, $boosterId, primary: false);
            if ($this->labLevel($planet, $now) <= 0) {
                throw InvalidLabSelectionException::boosterLabNotReady($boosterId);
            }
            $boosterPlanets[] = $planet;
        }

        return [$primary, $boosterPlanets];
    }

    private function lookupOwnedPlanet(Player $player, PlanetId $planetId, bool $primary): Planet
    {
        foreach ($player->getPlanets() as $planet) {
            if ($planet->getId()->equals($planetId)) {
                return $planet;
            }
        }
        throw $primary
            ? InvalidLabSelectionException::primaryNotOwned($planetId)
            : InvalidLabSelectionException::boosterNotOwned($planetId);
    }

    private function pickStrongestReadyLabPlanet(Player $player, \DateTimeImmutable $now): Planet
    {
        $bestPlanet = null;
        $bestLvl = 0;
        foreach ($player->getPlanets() as $planet) {
            $lvl = $this->labLevel($planet, $now);
            if ($lvl > $bestLvl) {
                $bestLvl = $lvl;
                $bestPlanet = $planet;
            }
        }
        if ($bestPlanet === null) {
            throw new ResearchLabMissingException();
        }

        return $bestPlanet;
    }

    /**
     * @return array<string, int>
     */
    private function aggregatePlayerResources(Player $player): array
    {
        $totals = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getResources() as $r) {
                $key = $r->getType()->value;
                $totals[$key] = ($totals[$key] ?? 0) + $r->getAmount();
            }
        }

        return $totals;
    }

    private function deductFromPlayer(Player $player, ResourceType $type, int $amount): void
    {
        foreach ($player->getPlanets() as $planet) {
            if ($amount <= 0) {
                return;
            }
            foreach ($planet->getResources() as $r) {
                if ($r->getType() !== $type) {
                    continue;
                }
                $take = min($amount, $r->getAmount());
                $r->setAmount($r->getAmount() - $take);
                $amount -= $take;
                if ($amount <= 0) {
                    return;
                }
            }
        }
    }
}
