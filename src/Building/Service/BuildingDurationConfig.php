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

        // Hub (30min)
        BuildingType::HUB->value => 1800,

        // Refinement (30min)
        BuildingType::IRON_SMELTER->value => 1800,

        // T-011: Raumwerft (60min — Strategic-Building)
        BuildingType::SHIPYARD->value => 3600,

        // T-013: Probe-Lab (30min)
        BuildingType::PROBE_LAB->value => 1800,

        // T-021: Recycling-Plant (30min)
        BuildingType::RECYCLING_PLANT->value => 1800,

        // Storage (15min — moderate)
        BuildingType::IRON_STORAGE->value => 900,
        BuildingType::COAL_STORAGE->value => 900,
        BuildingType::IRON_BAR_STORAGE->value => 900,
        BuildingType::WATER_TANK->value => 900,
        BuildingType::FOOD_SILO->value => 900,
        BuildingType::OXYGEN_STORAGE->value => 900,
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
