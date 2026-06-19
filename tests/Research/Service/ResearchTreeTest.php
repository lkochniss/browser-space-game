<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Service\ResearchTree;
use PHPUnit\Framework\TestCase;

final class ResearchTreeTest extends TestCase
{
    public function test_stub_nodes_registered(): void
    {
        $tree = new ResearchTree();

        self::assertTrue($tree->has('mining_efficiency_1'));
        self::assertTrue($tree->has('ftl_tier_1'));
        // T-025 Stubs (2) + T-170 Tier-1 (6) + T-026 Antrieb (7) + T-064 Bauzeit-Boost (1) = 16
        self::assertCount(16, $tree->all());
        self::assertTrue($tree->has('construction_speed_1'));
        self::assertTrue($tree->has('basic_mining'));
        self::assertTrue($tree->has('metallurgy'));
        self::assertTrue($tree->has('propulsion_hydrogen'));
        self::assertTrue($tree->has('ftl_hyperdrive'));
        self::assertTrue($tree->has('ftl_warp'));
        self::assertTrue($tree->has('ftl_jumpdrive'));
    }

    public function test_get_returns_node(): void
    {
        $tree = new ResearchTree();
        $node = $tree->get('mining_efficiency_1');

        self::assertSame('mining_efficiency_1', $node->slug);
        self::assertSame(3, $node->maxLevel);
    }

    public function test_get_unknown_throws(): void
    {
        $this->expectException(ResearchNodeNotFoundException::class);
        (new ResearchTree())->get('does_not_exist');
    }
}
