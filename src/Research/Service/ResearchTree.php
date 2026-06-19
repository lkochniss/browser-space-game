<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Model\ResearchNode;
use App\Resource\ValueObject\ResourceType;

/**
 * T-025 zentrale Research-Tree-Konfiguration.
 *
 * Foundation startet mit 2 Stub-Nodes — T-026 ergänzt echte Antrieb-Tree-
 * Nodes inkl. `ftl_tier_2` (für Wormhole-Travel-Lock). Wirkungs-Hooks
 * (z.B. Mining-Boost) folgen mit den jeweiligen Branch-Tickets (T-127ff).
 */
class ResearchTree
{
    /** @var array<string, ResearchNode> */
    private array $nodes;

    public function __construct()
    {
        $this->nodes = [];
        $this->register(new ResearchNode(
            slug: 'mining_efficiency_1',
            name: 'Mining-Effizienz I',
            description: 'Verbessert die Förderrate aller Mines (T-127 Hook).',
            baseDurationSeconds: 300,
            maxLevel: 3,
            prerequisites: [],
            resourceCostBase: [
                ResourceType::IRON_ORE->value => 100,
                ResourceType::COAL->value => 50,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'ftl_tier_1',
            name: 'FTL-Tier 1',
            description: 'Foundation für Antrieb-Tree (T-026). Voraussetzung für ftl_tier_2 → Wormholes.',
            baseDurationSeconds: 600,
            maxLevel: 1,
            prerequisites: [],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 200,
                ResourceType::SILICON->value => 100,
            ],
        ));
    }

    private function register(ResearchNode $node): void
    {
        $this->nodes[$node->slug] = $node;
    }

    public function get(string $slug): ResearchNode
    {
        if (!isset($this->nodes[$slug])) {
            throw new ResearchNodeNotFoundException($slug);
        }

        return $this->nodes[$slug];
    }

    public function has(string $slug): bool
    {
        return isset($this->nodes[$slug]);
    }

    /**
     * @return list<ResearchNode>
     */
    public function all(): array
    {
        return array_values($this->nodes);
    }
}
