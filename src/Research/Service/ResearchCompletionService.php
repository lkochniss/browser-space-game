<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Common\Interface\ClockInterface;
use App\Player\Model\Player;
use App\Research\Model\PlayerResearch;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-025 Wallclock-Resolver für Forschungs-Completion.
 *
 * Wird vom Demo-CLI / T-044 Tick-Scheduler nach `tickEngine.run` aufgerufen
 * (analog FleetArrivalService + TelescopeDiscoveryService).
 *
 * Pro fertiger ActiveResearch (`finished_at <= now`):
 *  - Upsert PlayerResearch.level für (player, nodeSlug)
 *  - Lösche ActiveResearch
 *
 * Returns Anzahl resolved.
 */
readonly class ResearchCompletionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActiveResearchRepository $activeRepository,
        private PlayerResearchRepository $playerRepository,
        private ClockInterface $clock,
    ) {
    }

    public function runTickForPlayer(Player $player): int
    {
        $active = $this->activeRepository->findActiveForPlayer($player);
        if ($active === null) {
            return 0;
        }

        $now = $this->clock->now();
        if (!$active->isFinished($now)) {
            return 0;
        }

        $existing = $this->playerRepository->findOneByPlayerAndSlug($player, $active->getNodeSlug());
        if ($existing === null) {
            $entry = PlayerResearch::generate($player, $active->getNodeSlug(), $active->getTargetLevel());
            $this->em->persist($entry);
        } else {
            $existing->incrementLevel();
        }

        $this->em->remove($active);
        $this->em->flush();

        return 1;
    }
}
