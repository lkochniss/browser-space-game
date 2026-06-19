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
        self::assertCount(2, $tree->all());
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
