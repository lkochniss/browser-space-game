<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Planet\Model\Planet;
use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;
use DateTimeImmutable;

/**
 * T-094c + T-094d: berechnet den effektiven Parallel-Build-Cap pro Planet.
 *
 * Formel:
 *   foundation = 3 (T-094 MAX_CONCURRENT_BUILDS)
 *   hqBonus    = HQ-Level / 5 (T-094c)
 *   logBonus   = logistics_1.level (T-094d, +1 pro Level)
 *   final      = min(8, foundation + hqBonus + logBonus)
 *
 * Hard-Cap 8 schließt beide Bonus-Quellen ein.
 */
readonly class BuildQueueCapCalculator
{
    public const HARD_CAP = 8;

    public function __construct(
        private PlayerResearchRepository $playerResearchRepository,
    ) {
    }

    public function compute(Planet $planet, ?Player $player, ?DateTimeImmutable $now): int
    {
        $base = $planet->getEffectiveBuildQueueCap($now); // T-094c HQ-Bonus
        if ($player === null) {
            return $base;
        }
        $logisticsLevel = $this->playerResearchRepository
            ->findOneByPlayerAndSlug($player, 'logistics_1')
            ?->getLevel() ?? 0;

        return min(self::HARD_CAP, $base + $logisticsLevel);
    }
}
