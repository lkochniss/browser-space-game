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
 * Cargo-Volume seit T-178 in `ShipCargoVolumeConfig`.
 */
class ShipCostConfig
{
    /** @var array<string, array{resources: array<string,int>, populationCost: int, durationSeconds: int}> */
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
            ],
            ShipType::COLONY_SHIP->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 300,
                ],
                'populationCost' => 50,
                'durationSeconds' => 3600, // 60min — Strategic-Tier
            ],

            // T-015 Transport-Klassen
            ShipType::TRANSPORT_SMALL->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 150,
                ],
                'populationCost' => 15,
                'durationSeconds' => 1800, // 30min
            ],
            ShipType::TRANSPORT_MEDIUM->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 400,
                    ResourceType::ALUMINUM_ORE->value => 50,
                ],
                'populationCost' => 30,
                'durationSeconds' => 3600, // 60min
            ],
            ShipType::TRANSPORT_LARGE->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 1000,
                    ResourceType::ALUMINUM_ORE->value => 200,
                    ResourceType::TITANIUM_ORE->value => 50,
                ],
                'populationCost' => 100,
                'durationSeconds' => 7200, // 120min — Heavy-Hauler
            ],

            // T-016 Bergungsschiff
            ShipType::SALVAGE->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 250,
                    ResourceType::ALUMINUM_ORE->value => 50,
                ],
                'populationCost' => 25,
                'durationSeconds' => 2700, // 45min
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

    /**
     * @return array{resources: array<string,int>, populationCost: int, durationSeconds: int}
     */
    private function require(ShipType $type): array
    {
        if (!isset($this->configs[$type->value])) {
            throw new LogicException(sprintf('No cost configured for ShipType "%s"', $type->value));
        }

        return $this->configs[$type->value];
    }
}
