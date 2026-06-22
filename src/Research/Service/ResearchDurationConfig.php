<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Research\Model\ResearchNode;

/**
 * T-025 Wallclock-Forschungs-Dauer.
 *
 * Formel:
 *   effectiveDuration = baseDuration × levelMultiplier(targetLevel) ÷ labSpeedMultiplier(effectiveLab)
 *
 * - levelMultiplier:  2^(targetLevel - 1)  → L1 = 1×, L2 = 2×, L3 = 4×, ... (analog Buildings)
 * - labSpeedMultiplier: stetig steigend mit Lab-Level via Exponentialfunktion:
 *      L1 = 1.00× (no boost)
 *      L2 = 1.18× (-15%)
 *      L3 = 1.39× (-28%)
 *      L4 = 1.64× (-39%)
 *      L5 = 1.93× (-48%)
 *      ...
 *      formelhaft: pow(1.18, effectiveLab - 1)  (akzeptiert float)
 *
 * T-025c Opt-In-Multi-Lab-Cost (ersetzt T-025b Auto-Aggregator):
 *
 *   baseScaled       = node.resourceCostBase × 2^(targetLevel - 1)
 *   N                = count(boosterLvls)
 *   mismatchPenalty  = sum( max(0, primaryLvl - boosterLvl)² )  // asymmetrisch
 *   costMultiplier   = 1 + (FLAT_BOOSTER_COST × N) + (MISMATCH_PENALTY × mismatchPenalty)
 *   finalCost        = baseScaled × costMultiplier
 *
 * Effekt:
 *   - jeder Booster kostet 10% Aufschlag (flat)
 *   - schwächere Booster relativ zum Primary kosten quadratisch mehr
 *   - stärkere Booster (über Primary) kosten nur den 10%-Flat (kein Penalty)
 *
 * Konstanten in `FLAT_BOOSTER_COST` + `MISMATCH_PENALTY` als Tuning-Knobs.
 */
class ResearchDurationConfig
{
    /** T-025c D2 — flacher Aufschlag pro Booster-Lab. */
    public const FLAT_BOOSTER_COST = 0.10;

    /** T-025c D2 — quadratische Mismatch-Penalty pro Level-Lücke (Primary − Booster). */
    public const MISMATCH_PENALTY = 0.02;

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
     * T-025c (D2) — Resource-Cost mit Multi-Lab-Aufschlag.
     *
     * @param list<int> $boosterLvls
     *
     * @return array<string, int>
     */
    public function resourceCost(
        ResearchNode $node,
        int $targetLevel,
        int $primaryLvl = 1,
        array $boosterLvls = [],
    ): array {
        $levelMultiplier = 2 ** ($targetLevel - 1);

        $n = count($boosterLvls);
        $mismatchPenalty = 0;
        foreach ($boosterLvls as $boosterLvl) {
            $gap = max(0, $primaryLvl - $boosterLvl);
            $mismatchPenalty += $gap * $gap;
        }
        $costMultiplier = 1.0 + (self::FLAT_BOOSTER_COST * $n) + (self::MISMATCH_PENALTY * $mismatchPenalty);

        $scaled = [];
        foreach ($node->resourceCostBase as $resourceVal => $amount) {
            $scaled[$resourceVal] = (int) ceil($amount * $levelMultiplier * $costMultiplier);
        }

        return $scaled;
    }
}
