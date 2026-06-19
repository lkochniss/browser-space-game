<?php

declare(strict_types=1);

namespace App\Research\Model;

/**
 * T-025 Research-Node: rein deklarative VO für einen Tech-Eintrag.
 *
 * `prerequisites` ist Liste von [slug, minLevel] Pairs (z.B. `[['mining_efficiency_1', 2]]`).
 * `resourceCostBase` ist Map<ResourceType-value, int> für Level 1; höhere Levels skalieren
 * via `ResearchDurationConfig::cost(targetLevel)` analog Building-Cost-Pattern.
 */
readonly class ResearchNode
{
    /**
     * @param list<array{slug: string, level: int}> $prerequisites
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
