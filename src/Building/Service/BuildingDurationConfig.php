<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\ValueObject\BuildingType;
use LogicException;

class BuildingDurationConfig
{
    /** @var array<string, int> Base-Bauzeit in Sekunden je BuildingType */
    private array $baseDurationSeconds = [
        // Mines (5min)
        BuildingType::IRON_MINE->value => 300,
        BuildingType::COAL_MINE->value => 300,
        BuildingType::COPPER_MINE->value => 300,
        BuildingType::SILICON_MINE->value => 300,
        BuildingType::ALUMINUM_MINE->value => 300,
        BuildingType::TITANIUM_MINE->value => 300,
        BuildingType::URANIUM_MINE->value => 300,

        // T-172: HQ heavy-Verwaltung (60min), HUB klein-Wohngebäude (15min)
        BuildingType::HQ->value => 3600,
        BuildingType::HUB->value => 900,

        // Refinement (30min)
        BuildingType::IRON_SMELTER->value => 1800,

        // T-011: Raumwerft (60min — Strategic-Building)
        BuildingType::SHIPYARD->value => 3600,

        // T-013: Probe-Lab (30min)
        BuildingType::PROBE_LAB->value => 1800,

        // T-021: Recycling-Plant (30min)
        BuildingType::RECYCLING_PLANT->value => 1800,

        // T-018: Teleskop (45min)
        BuildingType::TELESCOPE->value => 2700,

        // T-025: Research-Lab (45min)
        BuildingType::RESEARCH_LAB->value => 2700,

        // T-177: WAREHOUSE konsolidiert T-061 (6 Storage-Buildings gelöscht; 15min)
        BuildingType::WAREHOUSE->value => 900,

        // T-097a: Renewable-Producer (15min — analog Storage)
        BuildingType::WATER_RECLAIMER->value => 900,
        BuildingType::AGRI_DOME->value => 900,
        BuildingType::ATMOSPHERIC_PROCESSOR->value => 900,

        // T-064b → T-172 Rename: Construction-Yard (30min Strategic-Tier)
        BuildingType::CONSTRUCTION_YARD->value => 1800,

        // T-070 Pop-QoL-Buildings (T-182: UNIVERSITY entfernt — Wort-Mix-Up mit RESEARCH_LAB)
        BuildingType::HOSPITAL->value => 1800,        // 30min
        BuildingType::CULTURAL_CENTER->value => 1800, // 30min
        BuildingType::TEMPLE->value => 1200,          // 20min (kleines QoL)

        // T-067 Tier-2 Mines (5min — wie Tier-1-Mines)
        BuildingType::PLASTIC_RESIN_MINE->value => 300,
        BuildingType::TRITIUM_MINE->value => 300,

        // T-067 Tier-2 Refineries (Bars 30min, Compounds 35-60min)
        BuildingType::ALUMINUM_REFINERY->value => 1800,
        BuildingType::COPPER_REFINERY->value => 1800,
        BuildingType::TITANIUM_REFINERY->value => 1800,
        BuildingType::STEEL_SMELTER->value => 1800,
        BuildingType::CHIP_FAB->value => 2400,         // 40min — Hightech
        BuildingType::COMPOSITE_PLANT->value => 2100,  // 35min
        BuildingType::HULL_FOUNDRY->value => 3600,     // 60min — Heavy
        BuildingType::SHIELD_ASSEMBLER->value => 2700, // 45min
    ];

    /**
     * Returns construction duration in seconds.
     *
     * - $currentLevel = 0 → initial build (base × 2^0 = base)
     * - $currentLevel = N → upgrade from N to N+1 (base × 2^N)
     *
     * Skaliert exponentiell wie BuildingCostConfig::getCost (T-010-konsistent).
     */
    public function getDurationSeconds(BuildingType $type, int $currentLevel = 0): int
    {
        if (!isset($this->baseDurationSeconds[$type->value])) {
            throw new LogicException(sprintf('No duration configured for "%s"', $type->value));
        }
        if ($currentLevel < 0) {
            throw new LogicException(sprintf('currentLevel must be >= 0, got %d', $currentLevel));
        }

        return $this->baseDurationSeconds[$type->value] * (2 ** $currentLevel);
    }
}
