<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Service\ResearchTree;
use PHPUnit\Framework\TestCase;

final class ResearchTreeTest extends TestCase
{
    public function test_all_nodes_registered(): void
    {
        $tree = new ResearchTree();

        // T-170 Tier-1 (6) + T-026 Antrieb (7) + T-064 Bauzeit-Boost (1) + T-094d Logistics (1) = 15
        self::assertCount(15, $tree->all());
        self::assertTrue($tree->has('logistics_1'));

        // T-170
        self::assertTrue($tree->has('basic_mining'));
        self::assertTrue($tree->has('metallurgy'));
        self::assertTrue($tree->has('astronomy'));
        self::assertTrue($tree->has('shipbuilding'));
        self::assertTrue($tree->has('advanced_mining'));
        self::assertTrue($tree->has('recycling'));

        // T-026 Antrieb-Tree
        self::assertTrue($tree->has('propulsion_hydrogen'));
        self::assertTrue($tree->has('propulsion_ion'));
        self::assertTrue($tree->has('propulsion_fusion'));
        self::assertTrue($tree->has('propulsion_antimatter'));
        self::assertTrue($tree->has('ftl_hyperdrive'));
        self::assertTrue($tree->has('ftl_warp'));
        self::assertTrue($tree->has('ftl_jumpdrive'));

        // T-064
        self::assertTrue($tree->has('construction_speed_1'));
    }

    public function test_get_returns_node(): void
    {
        $tree = new ResearchTree();
        $node = $tree->get('basic_mining');

        self::assertSame('basic_mining', $node->slug);
        self::assertSame(1, $node->maxLevel);
    }

    public function test_get_unknown_throws(): void
    {
        $this->expectException(ResearchNodeNotFoundException::class);
        (new ResearchTree())->get('does_not_exist');
    }
}
