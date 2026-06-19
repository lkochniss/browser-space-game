<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Resource\ValueObject\ResourceType;

/**
 * T-021 Wahrscheinlichkeits-Tabelle für Recycling-Output pro DEBRIS-Tier.
 *
 * Per Trümmer-Unit (1 DEBRIS_LOW etc.) wird ein gewichtetes Resource-Sample
 * gezogen. `null` als Output bedeutet "nichts gewonnen" (Trümmer war Schrott).
 */
class RecyclingTable
{
    /**
     * @return list<array{weight: int, output: ?ResourceType, minAmount: int, maxAmount: int}>
     */
    public function entries(ResourceType $debrisType): array
    {
        return match ($debrisType) {
            ResourceType::DEBRIS_LOW => [
                ['weight' => 70, 'output' => ResourceType::IRON_ORE,    'minAmount' => 5,  'maxAmount' => 15],
                ['weight' => 20, 'output' => ResourceType::COAL,        'minAmount' => 3,  'maxAmount' => 10],
                ['weight' => 10, 'output' => null,                      'minAmount' => 0,  'maxAmount' => 0],
            ],
            ResourceType::DEBRIS_MEDIUM => [
                ['weight' => 50, 'output' => ResourceType::IRON_BAR,     'minAmount' => 3,  'maxAmount' => 8],
                ['weight' => 30, 'output' => ResourceType::SILICON,      'minAmount' => 5,  'maxAmount' => 15],
                ['weight' => 15, 'output' => ResourceType::TITANIUM_ORE, 'minAmount' => 2,  'maxAmount' => 5],
                ['weight' => 5,  'output' => null,                       'minAmount' => 0,  'maxAmount' => 0],
            ],
            ResourceType::DEBRIS_HIGH => [
                ['weight' => 40, 'output' => ResourceType::TITANIUM_ORE, 'minAmount' => 5,  'maxAmount' => 12],
                ['weight' => 30, 'output' => ResourceType::URANIUM_ORE,  'minAmount' => 3,  'maxAmount' => 8],
                ['weight' => 20, 'output' => ResourceType::IRON_BAR,     'minAmount' => 8,  'maxAmount' => 20],
                ['weight' => 10, 'output' => ResourceType::ALUMINUM_ORE, 'minAmount' => 10, 'maxAmount' => 25],
            ],
            default => [],
        };
    }
}
