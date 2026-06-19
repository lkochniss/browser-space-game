<?php

declare(strict_types=1);

namespace App\Common\Service;

/**
 * T-151 Anti-Run-Away-Soft-Caps. Sanfte Diminishing-Returns auf 3 Achsen.
 *
 * - Pop-Wachstum: ab 1M Pop graduelle Drosselung → min 0.1× bei extrem hohem Pop
 * - Building-Cost: ab Level 20 zusätzlicher exponentieller Faktor 1.05^(lvl-20)
 * - Mining-Effizienz: ab 100k Stockpile pro ResourceType graduelle Drosselung → min 0.5×
 *
 * Werte sind über Constants konfigurierbar für Tuning.
 */
class SoftCapConfig
{
    public const POP_GROWTH_THRESHOLD = 1_000_000;
    public const POP_GROWTH_DENOMINATOR = 1_000_000_000;
    public const POP_GROWTH_MIN_MULTIPLIER = 0.1;

    public const BUILDING_COST_THRESHOLD_LEVEL = 20;
    public const BUILDING_COST_EXPONENT_BASE = 1.05;

    public const STOCKPILE_THRESHOLD = 100_000;
    public const STOCKPILE_DENOMINATOR = 1_000_000;
    public const STOCKPILE_MIN_MULTIPLIER = 0.5;

    /**
     * Pop > threshold reduziert das Pop-Wachstum.
     * Beispiel: pop=2M → 0.999, pop=500M → 0.5, pop=10B → 0.1 (geclampt)
     */
    public function popGrowthMultiplier(int $popTotal): float
    {
        if ($popTotal <= self::POP_GROWTH_THRESHOLD) {
            return 1.0;
        }
        $excess = $popTotal - self::POP_GROWTH_THRESHOLD;
        $multiplier = 1.0 - ($excess / self::POP_GROWTH_DENOMINATOR);

        return max(self::POP_GROWTH_MIN_MULTIPLIER, $multiplier);
    }

    /**
     * Building-Cost-Multiplier für Level >= 20.
     * Beispiel: level=20 → 1.0, level=21 → 1.05, level=30 → 1.05^10 ≈ 1.63
     */
    public function buildingCostMultiplier(int $currentLevel): float
    {
        if ($currentLevel <= self::BUILDING_COST_THRESHOLD_LEVEL) {
            return 1.0;
        }
        $exponent = $currentLevel - self::BUILDING_COST_THRESHOLD_LEVEL;

        return self::BUILDING_COST_EXPONENT_BASE ** $exponent;
    }

    /**
     * Mining-Multiplier basierend auf aktuellem Stockpile pro ResourceType.
     * Beispiel: stockpile=100k → 1.0, stockpile=200k → 0.9, stockpile=600k+ → 0.5 (geclampt)
     */
    public function miningMultiplier(int $stockpile): float
    {
        if ($stockpile <= self::STOCKPILE_THRESHOLD) {
            return 1.0;
        }
        $excess = $stockpile - self::STOCKPILE_THRESHOLD;
        $multiplier = 1.0 - ($excess / self::STOCKPILE_DENOMINATOR);

        return max(self::STOCKPILE_MIN_MULTIPLIER, $multiplier);
    }
}
