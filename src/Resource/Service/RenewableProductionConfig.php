<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;

/**
 * T-097a: Base-Production-Rate pro Tick und Level für Renewable-Producer.
 *
 * Mapping:
 *   WATER_RECLAIMER       → +10 WATER pro Tick × Level
 *   AGRI_DOME             → +6 FOOD pro Tick × Level
 *   ATMOSPHERIC_PROCESSOR → +6 OXYGEN pro Tick × Level
 *
 * Verbrauch zur Referenz: 50 Pop × 0.1 = 5 W/F per Tick. L1 deckt + kleiner Surplus.
 */
class RenewableProductionConfig
{
    /**
     * @return array<int, array{building: BuildingType, resource: ResourceType, baseRate: int}>
     */
    public function entries(): array
    {
        return [
            ['building' => BuildingType::WATER_RECLAIMER, 'resource' => ResourceType::WATER, 'baseRate' => 10],
            ['building' => BuildingType::AGRI_DOME, 'resource' => ResourceType::FOOD, 'baseRate' => 6],
            ['building' => BuildingType::ATMOSPHERIC_PROCESSOR, 'resource' => ResourceType::OXYGEN, 'baseRate' => 6],
        ];
    }
}
