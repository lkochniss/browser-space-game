<?php

declare(strict_types=1);

namespace App\Planet\ValueObject;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;

enum PlanetType: string
{
    case TERRAN = 'terran';
    case BARREN = 'barren';
    case ICE = 'ice';
    case GAS_GIANT = 'gas_giant';
    case OCEAN = 'ocean';
    case VOLCANIC = 'volcanic';
    case DESERT = 'desert';

    /**
     * Multiplier on per-capita Renewable consumption.
     * 1.0 = neutral. >1 = Malus (mehr Verbrauch). <1 = Bonus (weniger Verbrauch).
     */
    public function getConsumptionMultiplier(ResourceType $resource): float
    {
        $multipliers = match ($this) {
            self::TERRAN => [],
            self::DESERT => [
                ResourceType::WATER->value => 1.5,
                ResourceType::FOOD->value => 1.5,
            ],
            self::OCEAN => [
                ResourceType::WATER->value => 0.5,
            ],
            self::ICE => [
                ResourceType::WATER->value => 0.5,
                ResourceType::FOOD->value => 1.2,
            ],
            self::VOLCANIC => [
                ResourceType::WATER->value => 1.3,
                ResourceType::FOOD->value => 1.2,
            ],
            self::BARREN => [
                ResourceType::FOOD->value => 1.5,
            ],
            self::GAS_GIANT => [],
        };

        return $multipliers[$resource->value] ?? 1.0;
    }

    /**
     * @return array<string, int> Map ResourceType.value → base deposit amount before size-multiplier.
     */
    public function getBaseDeposits(): array
    {
        return match ($this) {
            self::TERRAN => [
                ResourceType::IRON_ORE->value => 500,
                ResourceType::COAL->value => 300,
            ],
            self::BARREN => [
                ResourceType::IRON_ORE->value => 1500,
                ResourceType::COPPER_ORE->value => 800,
            ],
            self::ICE => [
                ResourceType::SILICON->value => 400,
            ],
            self::GAS_GIANT => [],
            self::OCEAN => [
                ResourceType::ALUMINUM_ORE->value => 600,
            ],
            self::VOLCANIC => [
                ResourceType::URANIUM_ORE->value => 500,
                ResourceType::IRON_ORE->value => 800,
            ],
            self::DESERT => [
                ResourceType::SILICON->value => 1000,
                ResourceType::TITANIUM_ORE->value => 300,
            ],
        };
    }

    /**
     * @return array<string, int> Map ResourceType.value → final deposit amount, scaled by size.
     */
    public function generateDeposits(PlanetSize $size): array
    {
        $multi = $size->getDepositMultiplier();
        $result = [];
        foreach ($this->getBaseDeposits() as $resourceTypeValue => $amount) {
            $result[$resourceTypeValue] = (int) ceil($amount * $multi);
        }

        return $result;
    }

    /**
     * T-063: Mining-Production-Bonus pro ResourceType.
     * 0.0 = neutral (TERRAN), positive = boost, negative = malus.
     * Effective Multiplier = max(0, 1 + bonus × sizeFactor).
     */
    public function getMiningBonus(ResourceType $resource): float
    {
        $bonuses = match ($this) {
            self::TERRAN => [],
            self::BARREN => [
                ResourceType::IRON_ORE->value => 0.5,
                ResourceType::COPPER_ORE->value => 0.5,
            ],
            self::DESERT => [
                ResourceType::SILICON->value => 1.0,
                ResourceType::TITANIUM_ORE->value => 0.5,
            ],
            self::ICE => [
                ResourceType::SILICON->value => 0.5,
            ],
            self::VOLCANIC => [
                ResourceType::URANIUM_ORE->value => 1.0,
                ResourceType::IRON_ORE->value => 0.5,
            ],
            self::OCEAN => [
                ResourceType::ALUMINUM_ORE->value => 0.5,
            ],
            self::GAS_GIANT => [
                // alle Mining-Resourcen → -1.0 → multiplier 0 (kein Mining)
                ResourceType::IRON_ORE->value => -1.0,
                ResourceType::COAL->value => -1.0,
                ResourceType::COPPER_ORE->value => -1.0,
                ResourceType::SILICON->value => -1.0,
                ResourceType::ALUMINUM_ORE->value => -1.0,
                ResourceType::TITANIUM_ORE->value => -1.0,
                ResourceType::URANIUM_ORE->value => -1.0,
            ],
        };

        return $bonuses[$resource->value] ?? 0.0;
    }

    /**
     * T-063: Refinement-Output-Bonus. Heute überall 0 (Tuning-Punkt für später).
     */
    public function getRefinementBonus(ResourceType $resource): float
    {
        return 0.0;
    }

    /**
     * T-063: Pop-Growth-Bonus auf Logistic-Wachstumsrate.
     * Multipliziert in `PopulationConsumptionProcessor` mit base-rate.
     */
    public function getPopGrowthBonus(): float
    {
        return match ($this) {
            self::TERRAN => 0.2,
            self::OCEAN => 0.1,
            self::BARREN => -0.1,
            self::VOLCANIC => -0.1,
            self::DESERT => -0.2,
            self::ICE => -0.3,
            self::GAS_GIANT => -0.5,
        };
    }

    /**
     * T-063: Construction-Speed-Bonus pro BuildingType.
     * Positive Werte = schneller Bau (Duration sinkt).
     */
    public function getConstructionSpeedBonus(BuildingType $building): float
    {
        if ($this === self::BARREN) {
            $isMine = match ($building) {
                BuildingType::IRON_MINE,
                BuildingType::COAL_MINE,
                BuildingType::COPPER_MINE,
                BuildingType::SILICON_MINE,
                BuildingType::ALUMINUM_MINE,
                BuildingType::TITANIUM_MINE,
                BuildingType::URANIUM_MINE => true,
                default => false,
            };
            return $isMine ? 0.2 : 0.0;
        }

        return 0.0;
    }
}
