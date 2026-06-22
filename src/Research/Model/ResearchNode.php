<?php

declare(strict_types=1);

namespace App\Research\Model;

use App\Research\Model\Prerequisite\ResearchPrerequisite;

/**
 * T-025 Research-Node (rein deklarative VO).
 * T-170: prerequisites umgestellt auf polymorphes ResearchPrerequisite-Interface
 *        (Research-Levels + Building-Levels).
 *
 * `resourceCostBase` ist Map<ResourceType-value, int> für Level 1; höhere Levels
 * skalieren via `ResearchDurationConfig::resourceCost(targetLevel)` analog
 * Building-Cost-Pattern.
 *
 * T-069: `requiredLabLevel` definiert das minimum effective Lab-Level (Primary
 * + Booster-Beitrag, T-025c) das Player haben muss um diesen Node zu starten.
 * Tier-Gating-Pattern: Tier-1 = L1, Tier-2 = L2, Tier-3 (Endgame) = L3.
 */
readonly class ResearchNode
{
    /**
     * @param list<ResearchPrerequisite> $prerequisites
     * @param array<string, int> $resourceCostBase
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public int $baseDurationSeconds,
        public int $maxLevel,
        public array $prerequisites = [],
        public array $resourceCostBase = [],
        public int $requiredLabLevel = 1,
    ) {
    }
}
