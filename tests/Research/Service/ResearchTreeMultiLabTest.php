<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Research\Service\ResearchTree;
use PHPUnit\Framework\TestCase;

/**
 * T-025c (D1) — `computeEffectiveLabLevel` als pure Funktion.
 *
 * Formel: primaryLvl + sum_i(sorted_desc[i] × 0.5^(i+1))
 */
final class ResearchTreeMultiLabTest extends TestCase
{
    public function test_no_booster_returns_primary(): void
    {
        $tree = new ResearchTree();
        self::assertEqualsWithDelta(7.0, $tree->computeEffectiveLabLevel(7, []), 0.0001);
        self::assertEqualsWithDelta(1.0, $tree->computeEffectiveLabLevel(1, []), 0.0001);
    }

    public function test_single_booster_l10_adds_5(): void
    {
        $tree = new ResearchTree();
        // 10 + 10 × 0.5 = 15.0
        self::assertEqualsWithDelta(15.0, $tree->computeEffectiveLabLevel(10, [10]), 0.0001);
    }

    public function test_three_boosters_geometric_decay(): void
    {
        $tree = new ResearchTree();
        // 10 + 10×0.5 + 8×0.25 + 1×0.125 = 10 + 5 + 2 + 0.125 = 17.125
        self::assertEqualsWithDelta(17.125, $tree->computeEffectiveLabLevel(10, [10, 8, 1]), 0.0001);
    }

    public function test_boosters_sorted_desc_for_max_bonus(): void
    {
        $tree = new ResearchTree();
        // Input-Reihenfolge irrelevant, immer descending sortiert verarbeitet:
        // [1, 8, 10] → effective = same as [10, 8, 1]
        self::assertEqualsWithDelta(
            $tree->computeEffectiveLabLevel(10, [10, 8, 1]),
            $tree->computeEffectiveLabLevel(10, [1, 8, 10]),
            0.0001,
        );
    }

    public function test_all_l1_diminishing_returns(): void
    {
        $tree = new ResearchTree();
        // 1 + 1×0.5 + 1×0.25 + 1×0.125 = 1.875
        self::assertEqualsWithDelta(1.875, $tree->computeEffectiveLabLevel(1, [1, 1, 1]), 0.0001);
    }

    public function test_many_boosters_converge(): void
    {
        $tree = new ResearchTree();
        // Geometric sum konvergiert: 1 + 0.5 + 0.25 + 0.125 + 0.0625 = 1.9375
        $eff = $tree->computeEffectiveLabLevel(1, [1, 1, 1, 1]);
        self::assertEqualsWithDelta(1.9375, $eff, 0.0001);
        self::assertLessThan(2.0, $eff, 'Geometric sum (Anchor=1) konvergiert gegen 2');
    }
}
