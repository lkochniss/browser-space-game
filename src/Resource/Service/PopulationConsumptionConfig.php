<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Resource\ValueObject\ResourceType;

class PopulationConsumptionConfig
{
    /**
     * @var array<string, float> per-capita-consumption per Tick
     */
    private array $perCapita = [
        ResourceType::WATER->value => 0.1,
        ResourceType::FOOD->value => 0.1,
    ];

    private float $logisticGrowthRate = 0.1;

    public function getPerCapita(ResourceType $resource): float
    {
        return $this->perCapita[$resource->value] ?? 0.0;
    }

    public function getLogisticGrowthRate(): float
    {
        return $this->logisticGrowthRate;
    }
}
