<?php

declare(strict_types=1);

namespace App\Planet\Service;

use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;

/**
 * T-101: Maximum-Planet-Cap pro Player.
 *
 * Formel:
 *   foundation = 5 (BASE_CAP)
 *   logBonus   = logistics_1.level (T-094d, +1 pro Level, max 3)
 *   final      = min(HARD_CAP, BASE_CAP + logBonus)
 *
 * `HARD_CAP = 10` lässt Raum für spätere Tier-3-Erweiterungen (T-136
 * Logistics-Branch); aktuelles maximales effektives Cap mit existing
 * `logistics_1` (Level 3) = 8.
 */
readonly class PlayerPlanetCapCalculator
{
    public const BASE_CAP = 5;
    public const HARD_CAP = 10;

    public function __construct(
        private PlayerResearchRepository $playerResearchRepository,
    ) {
    }

    public function compute(Player $player): int
    {
        $logisticsLevel = $this->playerResearchRepository
            ->findOneByPlayerAndSlug($player, 'logistics_1')
            ?->getLevel() ?? 0;

        return min(self::HARD_CAP, self::BASE_CAP + $logisticsLevel);
    }

    public function currentUsage(Player $player): int
    {
        return $player->getPlanets()->count();
    }
}
