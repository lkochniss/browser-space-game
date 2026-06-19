<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Research\Model\ResearchNode;
use App\Research\Service\ResearchDurationConfig;
use PHPUnit\Framework\TestCase;

final class ResearchDurationConfigTest extends TestCase
{
    public function test_level_1_no_lab_boost(): void
    {
        $config = new ResearchDurationConfig();
        $node = new ResearchNode('test', 'Test', '', 600, 5);

        // L1 + Lab L1: keine Skalierung weder oben noch unten
        self::assertSame(600, $config->durationSeconds($node, targetLevel: 1, maxLabLevel: 1));
    }

    public function test_level_skaliert_2_pow(): void
    {
        $config = new ResearchDurationConfig();
        $node = new ResearchNode('test', 'Test', '', 600, 5);

        // L2 = 600 × 2 = 1200
        self::assertSame(1200, $config->durationSeconds($node, 2, 1));
        // L3 = 600 × 4 = 2400
        self::assertSame(2400, $config->durationSeconds($node, 3, 1));
    }

    public function test_lab_speed_reduziert(): void
    {
        $config = new ResearchDurationConfig();
        $node = new ResearchNode('test', 'Test', '', 600, 5);

        // L1 + Lab L2: 600 / 1.18 = 508.5 → 509 (ceil)
        self::assertSame(509, $config->durationSeconds($node, 1, 2));
        // L1 + Lab L3: 600 / 1.18² ≈ 431
        self::assertSame(431, $config->durationSeconds($node, 1, 3));
    }

    public function test_lab_level_zero_falls_back_to_one(): void
    {
        $config = new ResearchDurationConfig();
        $node = new ResearchNode('test', 'Test', '', 600, 5);

        // 0 Lab → behandelt wie 1, kein NaN/0
        self::assertSame(600, $config->durationSeconds($node, 1, 0));
    }

    public function test_resource_cost_skaliert_2_pow(): void
    {
        $config = new ResearchDurationConfig();
        $node = new ResearchNode('test', 'Test', '', 300, 5, [], ['iron_ore' => 100, 'coal' => 50]);

        $costL1 = $config->resourceCost($node, 1);
        self::assertSame(['iron_ore' => 100, 'coal' => 50], $costL1);

        $costL2 = $config->resourceCost($node, 2);
        self::assertSame(['iron_ore' => 200, 'coal' => 100], $costL2);

        $costL3 = $config->resourceCost($node, 3);
        self::assertSame(['iron_ore' => 400, 'coal' => 200], $costL3);
    }
}
