<?php

declare(strict_types=1);

namespace App\Research\Service;

use App\Building\ValueObject\BuildingType;
use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Model\Prerequisite\BuildingLevelPrerequisite;
use App\Research\Model\Prerequisite\ResearchLevelPrerequisite;
use App\Research\Model\ResearchNode;
use App\Resource\ValueObject\ResourceType;

/**
 * T-025 zentrale Research-Tree-Konfiguration.
 * T-170: Tier-Mapping für Tech-Tree-Gating.
 *
 * Foundation-Stub-Nodes (mining_efficiency_1, ftl_tier_1) bleiben für
 * existing Tests + spätere Branch-Erweiterung (T-026). T-170 ergänzt 6
 * Tier-1-Nodes mit Building-Prereqs die echte Buildings unlocken.
 */
class ResearchTree
{
    /** @var array<string, ResearchNode> */
    private array $nodes;

    public function __construct()
    {
        $this->nodes = [];

        // T-025 Foundation-Stubs (kein Building-Effekt; bleiben für Tree-Tests + T-026 Folge)
        $this->register(new ResearchNode(
            slug: 'mining_efficiency_1',
            name: 'Mining-Effizienz I',
            description: 'Verbessert Förderrate (T-127 Hook).',
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

        // T-170 Tier-1 Tech-Tree-Nodes (gated buildings)
        $this->register(new ResearchNode(
            slug: 'basic_mining',
            name: 'Grundlagen-Bergbau',
            description: 'Schaltet Tier-1-Mines + Storage frei.',
            baseDurationSeconds: 240,
            maxLevel: 1,
            prerequisites: [
                new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_ORE->value => 100,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'metallurgy',
            name: 'Metallurgie',
            description: 'Schaltet Iron-Smelter + Bar-Storage frei.',
            baseDurationSeconds: 480,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('basic_mining', 1),
                new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 2),
            ],
            resourceCostBase: [
                ResourceType::IRON_ORE->value => 200,
                ResourceType::COAL->value => 100,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'astronomy',
            name: 'Astronomie',
            description: 'Schaltet Telescope + Probe-Lab frei.',
            baseDurationSeconds: 480,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('basic_mining', 1),
                new BuildingLevelPrerequisite(BuildingType::HUB, 2),
            ],
            resourceCostBase: [
                ResourceType::IRON_ORE->value => 150,
                ResourceType::COPPER_ORE->value => 80,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'shipbuilding',
            name: 'Schiffbau',
            description: 'Schaltet Shipyard frei.',
            baseDurationSeconds: 720,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('metallurgy', 1),
                new BuildingLevelPrerequisite(BuildingType::IRON_SMELTER, 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 100,
                ResourceType::COPPER_ORE->value => 100,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'advanced_mining',
            name: 'Tier-2-Bergbau',
            description: 'Schaltet Silicon/Aluminum/Titanium/Uranium-Mines frei.',
            baseDurationSeconds: 960,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('metallurgy', 1),
                new BuildingLevelPrerequisite(BuildingType::IRON_SMELTER, 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 150,
                ResourceType::COPPER_ORE->value => 150,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'recycling',
            name: 'Recycling-Verfahren',
            description: 'Schaltet Recycling-Plant frei.',
            baseDurationSeconds: 480,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('basic_mining', 1),
                new BuildingLevelPrerequisite(BuildingType::HUB, 2),
            ],
            resourceCostBase: [
                ResourceType::IRON_ORE->value => 150,
                ResourceType::COPPER_ORE->value => 100,
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
