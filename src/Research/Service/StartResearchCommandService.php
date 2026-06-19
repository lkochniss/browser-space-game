<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Research\Exception\AlreadyResearchingException;
use App\Research\Exception\InsufficientResearchResourcesException;
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
 * T-025 Validation + Effect für Forschungs-Start.
 *
 * Validation-Reihenfolge:
 *  1. Player existiert (sonst Doctrine-Exception, Foundation-Stub)
 *  2. Mindestens 1 fertiges RESEARCH_LAB auf irgendeinem Player-Planet
 *  3. Keine andere ActiveResearch des Players (1-zur-Zeit-Decision)
 *  4. Node existiert in ResearchTree (über `tree->get` → eigene Exception)
 *  5. PlayerResearch-Level < node.maxLevel
 *  6. Alle Prerequisites erfüllt (PlayerResearch.slug.level >= prereq.level)
 *  7. Resources (über alle Player-Planeten verteilt) reichen
 *
 * Effekt: Resources abziehen (FIFO über Planeten), ActiveResearch persistieren
 * mit `finished_at = now + duration`.
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

    public function getPlayerResearchLevel(\App\Player\Model\Player $player, string $nodeSlug): int
    {
        return $this->playerResearchRepository
            ->findOneByPlayerAndSlug($player, $nodeSlug)
            ?->getLevel() ?? 0;
    }

    public function __invoke(PlayerId $playerId, string $nodeSlug): ActiveResearch
    {
        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new \RuntimeException(sprintf('Player %s nicht gefunden', $playerId));
        }

        $now = $this->clock->now();

        // 2. Lab-Gate
        $effectiveLab = $this->getEffectiveLabLevel($player, $now);
        if ($effectiveLab < 1.0) {
            throw new ResearchLabMissingException();
        }

        // 3. Aktive Forschung?
        $active = $this->activeResearchRepository->findActiveForPlayer($player);
        if ($active !== null) {
            throw new AlreadyResearchingException($active->getNodeSlug());
        }

        // 4. Node existiert
        $node = $this->tree->get($nodeSlug); // throws ResearchNodeNotFoundException

        // 5. Max-Level
        $current = $this->playerResearchRepository->findOneByPlayerAndSlug($player, $nodeSlug);
        $currentLevel = $current?->getLevel() ?? 0;
        $targetLevel = $currentLevel + 1;
        if ($targetLevel > $node->maxLevel) {
            throw new MaxLevelReachedException($nodeSlug, $node->maxLevel);
        }

        // 6. Prerequisites (T-170: polymorph — Research- + Building-Levels)
        foreach ($node->prerequisites as $prereq) {
            if (!$prereq->isMetBy($player, $now, $this)) {
                throw new PrerequisiteNotMetException($nodeSlug, $prereq);
            }
        }

        // 7. Resources prüfen
        $cost = $this->durationConfig->resourceCost($node, $targetLevel);
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

        // ActiveResearch persistieren
        $duration = $this->durationConfig->durationSeconds($node, $targetLevel, $effectiveLab);
        $finishedAt = $now->add(new \DateInterval(sprintf('PT%dS', $duration)));
        $entry = ActiveResearch::generate($player, $nodeSlug, $targetLevel, $now, $finishedAt);
        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    /**
     * T-025b Multi-Lab-Aggregator. Sammelt alle ready Lab-Levels über alle Player-
     * Planeten, sortiert desc, summiert mit geometrischer Decay (Faktor 0.5):
     *
     *   effective = L1 × 1.0 + L2 × 0.5 + L3 × 0.25 + L4 × 0.125 + ...
     *
     * Ergebnis als float in `ResearchDurationConfig::durationSeconds` konsumiert.
     * Public damit Demo-CLI denselben Wert anzeigen kann.
     */
    public function getEffectiveLabLevel(Player $player, \DateTimeImmutable $now): float
    {
        $levels = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getBuildings() as $b) {
                if ($b->getType() !== BuildingType::RESEARCH_LAB) {
                    continue;
                }
                if (!$b->isReady($now)) {
                    continue;
                }
                $levels[] = $b->getLevel();
            }
        }
        if ($levels === []) {
            return 0.0;
        }
        rsort($levels);
        $effective = 0.0;
        foreach ($levels as $idx => $level) {
            $effective += $level * (0.5 ** $idx);
        }

        return $effective;
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
