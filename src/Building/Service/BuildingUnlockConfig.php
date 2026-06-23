<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\ValueObject\BuildingType;

/**
 * T-170 Mapping BuildingType → required Research-Level zum Bauen.
 *
 * Tier-0 (frei): IRON_MINE, HUB, RESEARCH_LAB, WAREHOUSE, Renewable-Producer.
 * Alle anderen Buildings sind hinter Forschung versteckt.
 *
 * T-177: WAREHOUSE (konsolidierte Storage-Foundation) ist Tier-0; alle alten
 * 6 T-061-Storage-Buildings gelöscht.
 *
 * `null` aus `requiredResearch()` = no lock (Tier-0 oder noch ungated).
 */
class BuildingUnlockConfig
{
    /**
     * @var array<string, array{slug: string, level: int}>
     */
    private array $unlocks;

    public function __construct()
    {
        $this->unlocks = [
            // basic_mining unlocks
            BuildingType::COAL_MINE->value => ['slug' => 'basic_mining', 'level' => 1],
            BuildingType::COPPER_MINE->value => ['slug' => 'basic_mining', 'level' => 1],

            // metallurgy unlocks
            BuildingType::IRON_SMELTER->value => ['slug' => 'metallurgy', 'level' => 1],

            // astronomy unlocks
            BuildingType::TELESCOPE->value => ['slug' => 'astronomy', 'level' => 1],
            BuildingType::PROBE_LAB->value => ['slug' => 'astronomy', 'level' => 1],

            // shipbuilding unlocks
            BuildingType::SHIPYARD->value => ['slug' => 'shipbuilding', 'level' => 1],

            // advanced_mining unlocks
            BuildingType::SILICON_MINE->value => ['slug' => 'advanced_mining', 'level' => 1],
            BuildingType::ALUMINUM_MINE->value => ['slug' => 'advanced_mining', 'level' => 1],
            BuildingType::TITANIUM_MINE->value => ['slug' => 'advanced_mining', 'level' => 1],
            BuildingType::URANIUM_MINE->value => ['slug' => 'advanced_mining', 'level' => 1],

            // recycling unlocks
            BuildingType::RECYCLING_PLANT->value => ['slug' => 'recycling', 'level' => 1],

            // T-064b → T-172 Rename: construction_yard via metallurgy
            BuildingType::CONSTRUCTION_YARD->value => ['slug' => 'metallurgy', 'level' => 1],
        ];
    }

    /**
     * @return ?array{slug: string, level: int} null = no lock (Tier-0 oder noch ungated)
     */
    public function requiredResearch(BuildingType $type): ?array
    {
        return $this->unlocks[$type->value] ?? null;
    }
}
