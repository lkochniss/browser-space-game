<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Resource\ValueObject\ResourceType;
use App\Ship\ValueObject\ShipType;
use LogicException;

/**
 * T-014/T-015 Cost-Service pro Schiffsklasse.
 *
 * Werte sind Foundation-Tuning. T-102 Mark-Tier-System wird mit Forschungs-Locks
 * (T-128 Schiffbau-Branch) zusätzliche Cost-Multiplier einbringen.
 *
 * `cargoCapacity`: 0 für non-Transport (T-015), >0 für Transport-Schiffe.
 */
class ShipCostConfig
{
    /** @var array<string, array{resources: array<string,int>, populationCost: int, durationSeconds: int, cargoCapacity: int}> */
    private array $configs;

    public function __construct()
    {
        $this->configs = [
            ShipType::GENERIC->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 100,
                ],
                'populationCost' => 20,
                'durationSeconds' => 1800, // 30min
                'cargoCapacity' => 0,
            ],
            ShipType::COLONY_SHIP->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 300,
                ],
                'populationCost' => 50,
                'durationSeconds' => 3600, // 60min — Strategic-Tier
                'cargoCapacity' => 0,
            ],

            // T-015 Transport-Klassen
            ShipType::TRANSPORT_SMALL->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 150,
                ],
                'populationCost' => 15,
                'durationSeconds' => 1800, // 30min
                'cargoCapacity' => 1000,
            ],
            ShipType::TRANSPORT_MEDIUM->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 400,
                    ResourceType::ALUMINUM_ORE->value => 50,
                ],
                'populationCost' => 30,
                'durationSeconds' => 3600, // 60min
                'cargoCapacity' => 5000,
            ],
            ShipType::TRANSPORT_LARGE->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 1000,
                    ResourceType::ALUMINUM_ORE->value => 200,
                    ResourceType::TITANIUM_ORE->value => 50,
                ],
                'populationCost' => 100,
                'durationSeconds' => 7200, // 120min — Heavy-Hauler
                'cargoCapacity' => 20000,
            ],
        ];
    }

    /**
     * @return array<string,int>
     */
    public function getResourceCost(ShipType $type): array
    {
        return $this->require($type)['resources'];
    }

    public function getPopulationCost(ShipType $type): int
    {
        return $this->require($type)['populationCost'];
    }

    public function getDurationSeconds(ShipType $type): int
    {
        return $this->require($type)['durationSeconds'];
    }

    public function getCargoCapacity(ShipType $type): int
    {
        return $this->require($type)['cargoCapacity'];
    }

    /**
     * @return array{resources: array<string,int>, populationCost: int, durationSeconds: int, cargoCapacity: int}
     */
    private function require(ShipType $type): array
    {
        if (!isset($this->configs[$type->value])) {
            throw new LogicException(sprintf('No cost configured for ShipType "%s"', $type->value));
        }

        return $this->configs[$type->value];
    }
}
