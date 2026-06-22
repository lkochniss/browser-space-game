<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Research\Model\ResearchNode;
use App\Research\Service\ResearchDurationConfig;
use PHPUnit\Framework\TestCase;

/**
 * T-025c (D2) — Resource-Cost-Formel mit Multi-Lab-Aufschlag:
 *
 *   final = baseScaled × (1 + 0.10×N + 0.02×mismatchPenalty)
 *   mismatchPenalty = sum( max(0, primary - booster)² )
 *
 * Test-Base: 1000 iron_ore, targetLevel=1 (× 1).
 */
final class ResearchDurationConfigCostTest extends TestCase
{
    private function makeNode(): ResearchNode
    {
        return new ResearchNode('test', 'Test', '', 600, 5, [], ['iron_ore' => 1000]);
    }

    public function test_no_booster_no_multiplier(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=10, boosters=[] → ×1.00
        $cost = $config->resourceCost($node, targetLevel: 1, primaryLvl: 10);
        self::assertSame(['iron_ore' => 1000], $cost);
    }

    public function test_booster_l10_with_primary_l10_only_flat(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=10, boosters=[10] → mismatchPenalty=0, ×(1 + 0.10×1) = ×1.10
        $cost = $config->resourceCost($node, 1, 10, [10]);
        self::assertSame(['iron_ore' => 1100], $cost);
    }

    public function test_booster_l5_with_primary_l10_grenzwertig(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=10, boosters=[5] → gap=5, penalty=25, ×(1 + 0.10 + 0.02×25) = ×1.60
        $cost = $config->resourceCost($node, 1, 10, [5]);
        self::assertSame(['iron_ore' => 1600], $cost);
    }

    public function test_booster_l1_with_primary_l10_unrentable(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=10, boosters=[1] → gap=9, penalty=81, ×(1 + 0.10 + 0.02×81) = ×2.72
        $cost = $config->resourceCost($node, 1, 10, [1]);
        self::assertSame(['iron_ore' => 2720], $cost);
    }

    public function test_two_boosters_both_strong(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=10, boosters=[10, 8]
        // gaps: 0, 2; penalty = 0 + 4 = 4
        // multiplier = 1 + 0.10×2 + 0.02×4 = 1.28
        $cost = $config->resourceCost($node, 1, 10, [10, 8]);
        self::assertSame(['iron_ore' => 1280], $cost);
    }

    public function test_booster_higher_than_primary_no_penalty(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // primary=5, boosters=[10] → gap=max(0, 5-10)=0, penalty=0
        // multiplier = 1 + 0.10 + 0 = 1.10
        $cost = $config->resourceCost($node, 1, 5, [10]);
        self::assertSame(['iron_ore' => 1100], $cost);
    }

    public function test_target_level_scales_base_before_multiplier(): void
    {
        $config = new ResearchDurationConfig();
        $node = $this->makeNode();

        // targetLevel=2 → baseScaled = 1000 × 2 = 2000
        // primary=10, boosters=[10] → ×1.10
        // → 2200
        $cost = $config->resourceCost($node, 2, 10, [10]);
        self::assertSame(['iron_ore' => 2200], $cost);
    }
}
