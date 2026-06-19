<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Research\Model\ResearchNode;

/**
 * T-025 Wallclock-Forschungs-Dauer.
 *
 * Formel:
 *   effectiveDuration = baseDuration × levelMultiplier(targetLevel) ÷ labSpeedMultiplier(maxLabLevel)
 *
 * - levelMultiplier:  2^(targetLevel - 1)  → L1 = 1×, L2 = 2×, L3 = 4×, ... (analog Buildings)
 * - labSpeedMultiplier: stetig steigend mit Lab-Level via geometric series:
 *      L1 = 1.00× (no boost)
 *      L2 = 1.18× (-15%)
 *      L3 = 1.39× (-28%)
 *      L4 = 1.64× (-39%)
 *      L5 = 1.93× (-48%)
 *      ...
 *      formelhaft: pow(1.18, level - 1)
 *
 * Cost (Resource-Cost) skaliert ebenfalls 2^(targetLevel - 1) analog Buildings.
 *
 * T-025b: Multi-Lab-Stacking via `effectiveLabLevel` (float). Aggregator
 *   sortiert alle Lab-Levels desc und summiert mit geometrischer Decay:
 *     effective = L_max × 1.0 + L_2 × 0.5 + L_3 × 0.25 + L_4 × 0.125 + ...
 *   Beispiele:
 *     [3]               → 3.0
 *     [2, 2]            → 2.0 + 1.0 = 3.0
 *     [3, 1, 1]         → 3.0 + 0.5 + 0.25 = 3.75
 *     [1, 1, 1, 1, 1]   → 1.0 + 0.5 + 0.25 + 0.125 + 0.0625 = 1.9375
 *
 * T-064: Forschungs-Buff für Bauzeit (separate Wirkung).
 */
class ResearchDurationConfig
{
    public function durationSeconds(ResearchNode $node, int $targetLevel, float $effectiveLabLevel): int
    {
        if ($effectiveLabLevel < 1.0) {
            // Lab fehlt — wird vom Service vor Aufruf abgefangen; Fallback = Default-Lab-Speed
            $effectiveLabLevel = 1.0;
        }
        $levelMultiplier = 2 ** ($targetLevel - 1);
        $labSpeed = pow(1.18, $effectiveLabLevel - 1);

        return (int) ceil($node->baseDurationSeconds * $levelMultiplier / $labSpeed);
    }

    /**
     * @return array<string, int>
     */
    public function resourceCost(ResearchNode $node, int $targetLevel): array
    {
        $multiplier = 2 ** ($targetLevel - 1);
        $scaled = [];
        foreach ($node->resourceCostBase as $resourceVal => $amount) {
            $scaled[$resourceVal] = (int) ceil($amount * $multiplier);
        }

        return $scaled;
    }
}
