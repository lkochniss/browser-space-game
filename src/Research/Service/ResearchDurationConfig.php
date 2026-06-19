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
 * T-025b (Folge): Multi-Lab-Stacking — bisher zählt nur das HÖCHSTE Lab-Level.
 * T-064: Forschungs-Buff für Bauzeit (separate Wirkung).
 */
class ResearchDurationConfig
{
    public function durationSeconds(ResearchNode $node, int $targetLevel, int $maxLabLevel): int
    {
        if ($maxLabLevel < 1) {
            // Lab fehlt — wird vom Service vor Aufruf abgefangen; Fallback = Default-Lab-Speed
            $maxLabLevel = 1;
        }
        $levelMultiplier = 2 ** ($targetLevel - 1);
        $labSpeed = pow(1.18, $maxLabLevel - 1);

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
