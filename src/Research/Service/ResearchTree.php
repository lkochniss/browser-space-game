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
            description: 'Foundation für Antrieb-Tree (T-026). Stub — T-026 nutzt eigene 7-Node-Chain ab propulsion_hydrogen.',
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

        // T-064: Bauzeit-Boost für alle Buildings. Multiplikativer Stack mit Planet-
        // Type-Bonus (T-063). 3 Levels — jede +10% Speed. L3 ≈ ×1.331 = -25% Duration.
        $this->register(new ResearchNode(
            slug: 'construction_speed_1',
            name: 'Effiziente Bauverfahren',
            description: 'Reduziert Bauzeit aller Gebäude (multiplikativ × 1.10 pro Level).',
            baseDurationSeconds: 600,
            maxLevel: 3,
            prerequisites: [
                new ResearchLevelPrerequisite('metallurgy', 1),
                new BuildingLevelPrerequisite(BuildingType::IRON_SMELTER, 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 150,
                ResourceType::COPPER_ORE->value => 100,
            ],
        ));

        // T-026 Antriebs-Tree: 4 Standard + 3 FTL.
        // Gating-Chain:
        //   shipbuilding (T-170) → SHIPYARD baubar
        //   propulsion_hydrogen (Standard-Foundation, braucht SHIPYARD L1)
        //   propulsion_ion → propulsion_fusion → propulsion_antimatter
        //   ftl_hyperdrive (braucht propulsion_fusion L1) — schaltet Inter-System-Travel frei
        //   ftl_warp (braucht ftl_hyperdrive L1) — Tier-2-FTL (Wormhole-Tech)
        //   ftl_jumpdrive (braucht ftl_warp L1) — Tier-3, Endgame
        $this->register(new ResearchNode(
            slug: 'propulsion_hydrogen',
            name: 'Wasserstoff-Antrieb',
            description: 'Foundation-Antrieb. Voraussetzung für höhere Standard-Antriebe + FTL-Tree.',
            baseDurationSeconds: 360,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('shipbuilding', 1),
                new BuildingLevelPrerequisite(BuildingType::SHIPYARD, 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 80,
                ResourceType::SILICON->value => 60,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'propulsion_ion',
            name: 'Ionen-Antrieb',
            description: 'Effizienter In-System-Antrieb (Tier-2 Standard).',
            baseDurationSeconds: 540,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('propulsion_hydrogen', 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 120,
                ResourceType::COPPER_ORE->value => 80,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'propulsion_fusion',
            name: 'Fusions-Antrieb',
            description: 'Hochleistungs-Standard (Tier-3). Voraussetzung für FTL.',
            baseDurationSeconds: 900,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('propulsion_ion', 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 200,
                ResourceType::TITANIUM_ORE->value => 80,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'propulsion_antimatter',
            name: 'Antimaterie-Antrieb',
            description: 'Endgame-Standard (Tier-4).',
            baseDurationSeconds: 1800,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('propulsion_fusion', 1),
            ],
            resourceCostBase: [
                ResourceType::URANIUM_ORE->value => 100,
                ResourceType::TITANIUM_ORE->value => 200,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'ftl_hyperdrive',
            name: 'Hyperraum-Antrieb (FTL Tier-1)',
            description: 'Schaltet Inter-System-Reise frei (Foundation-FTL).',
            baseDurationSeconds: 1200,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('propulsion_fusion', 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 300,
                ResourceType::URANIUM_ORE->value => 80,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'ftl_warp',
            name: 'Warp-Antrieb (FTL Tier-2)',
            description: 'Schnellere Inter-System-Reise; benötigt für Wormhole-Travel.',
            baseDurationSeconds: 1800,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('ftl_hyperdrive', 1),
            ],
            resourceCostBase: [
                ResourceType::IRON_BAR->value => 500,
                ResourceType::URANIUM_ORE->value => 150,
                ResourceType::TITANIUM_ORE->value => 100,
            ],
        ));
        $this->register(new ResearchNode(
            slug: 'ftl_jumpdrive',
            name: 'Sprungantrieb (FTL Tier-3)',
            description: 'Endgame-FTL. Quasi-instantane Sprünge.',
            baseDurationSeconds: 3600,
            maxLevel: 1,
            prerequisites: [
                new ResearchLevelPrerequisite('ftl_warp', 1),
                new ResearchLevelPrerequisite('propulsion_antimatter', 1),
            ],
            resourceCostBase: [
                ResourceType::URANIUM_ORE->value => 300,
                ResourceType::TITANIUM_ORE->value => 400,
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
