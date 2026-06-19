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
    ) {
    }
}
