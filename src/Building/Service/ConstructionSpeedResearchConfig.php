<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;

/**
 * T-064: Forschungs-Bauzeit-Boost.
 *
 * Aggregiert Bauzeit-Multiplikatoren aus Forschungs-Nodes (aktuell nur
 * `construction_speed_1`). Multiplikativ — stackt mit Planet-Type-Bonus
 * (T-063) im Build/Upgrade-Service.
 *
 * Decisions (siehe Ticket T-064):
 *  - Stacking: multiplikativ
 *  - Retroaktiv: nein — wirkt nur auf neu gestartete Bauten/Upgrades
 *  - Upgrades = Initial-Bauten (gleiche Multiplikator-Anwendung)
 *
 * Folge-Nodes (z.B. `construction_speed_2` für Tier-3) ergänzen hier mit
 * neuen Mappings; multiplikativer Stack aller Nodes.
 */
readonly class ConstructionSpeedResearchConfig
{
    private const SPEED_PER_LEVEL = 1.10;

    /** @var array<string, float> Map<node-slug, multiplier-per-level> */
    private const NODE_MULTIPLIERS = [
        'construction_speed_1' => self::SPEED_PER_LEVEL,
    ];

    public function __construct(
        private PlayerResearchRepository $playerResearchRepository,
    ) {
    }

    /**
     * Liefert kumulativen Speed-Multiplier (≥ 1.0) für alle relevanten Nodes
     * dieses Players. Multiplikativ über Nodes UND über deren Levels.
     *
     * Beispiel: construction_speed_1 L3 = 1.10³ ≈ 1.331 → -25% Bauzeit.
     */
    public function getMultiplier(?Player $player): float
    {
        if ($player === null) {
            return 1.0;
        }
        $multi = 1.0;
        foreach (self::NODE_MULTIPLIERS as $slug => $perLevel) {
            $level = $this->playerResearchRepository
                ->findOneByPlayerAndSlug($player, $slug)
                ?->getLevel() ?? 0;
            if ($level > 0) {
                $multi *= ($perLevel ** $level);
            }
        }

        return $multi;
    }
}
