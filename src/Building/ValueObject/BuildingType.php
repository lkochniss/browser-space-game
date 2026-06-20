<?php

declare(strict_types=1);

namespace App\Building\ValueObject;

use App\Resource\ValueObject\ResourceType;

enum BuildingType: string
{
    case IRON_MINE = 'iron_mine';
    case COAL_MINE = 'coal_mine';
    case COPPER_MINE = 'copper_mine';
    case SILICON_MINE = 'silicon_mine';
    case ALUMINUM_MINE = 'aluminum_mine';
    case TITANIUM_MINE = 'titanium_mine';
    case URANIUM_MINE = 'uranium_mine';

    case HUB = 'hub';

    case IRON_SMELTER = 'iron_smelter';

    case SHIPYARD = 'shipyard';

    case PROBE_LAB = 'probe_lab';

    // T-021: Recycling-Plant. Verarbeitet DEBRIS_* Cargo zu zufälligen FINITE/REFINED-Outputs.
    case RECYCLING_PLANT = 'recycling_plant';

    // T-018: Teleskop. Deckt pro Tick `level` zufällige unbekannte SolarSystems
    // für den Player auf (TelescopeDiscoveryProcessor).
    case TELESCOPE = 'telescope';

    // T-025: Research-Lab. Voraussetzung für Forschung; höheres Lab-Level
    // reduziert Forschungs-Dauer multiplikativ (1.18^(level-1)). T-069
    // erweitert um Tier-Gates für höhere Tech-Tiers.
    case RESEARCH_LAB = 'research_lab';

    case IRON_STORAGE = 'iron_storage';
    case COAL_STORAGE = 'coal_storage';
    case IRON_BAR_STORAGE = 'iron_bar_storage';
    case WATER_TANK = 'water_tank';
    case FOOD_SILO = 'food_silo';
    case OXYGEN_STORAGE = 'oxygen_storage';

    // T-097a: Renewable-Producer. Tier-0, ohne sie kein Pop-Survival.
    case WATER_RECLAIMER = 'water_reclaimer';
    case AGRI_DOME = 'agri_dome';
    case ATMOSPHERIC_PROCESSOR = 'atmospheric_processor';

    public function getPopulationCapBonusPerLevel(): int
    {
        return match ($this) {
            self::HUB => 50,
            default => 0,
        };
    }

    /**
     * T-171: Strikt-unique Buildings — max 1 Instanz pro Planet, Folge-Build wirft
     * `BuildingAlreadyExistsException`. Spieler nutzt Upgrade um Level zu steigern.
     *
     * Strategic + Lifelines sind unique; Mines/Storage/Producer/Smelter sind Multi.
     */
    public function isUnique(): bool
    {
        return match ($this) {
            self::HUB,
            self::RESEARCH_LAB,
            self::SHIPYARD,
            self::PROBE_LAB,
            self::RECYCLING_PLANT,
            self::TELESCOPE => true,
            default => false,
        };
    }

    /**
     * T-171: Slot-Size pro Building. Planet hat Slot-Cap (PlanetSize-abhängig);
     * Summe der gebauten + in-Bau-Building-Sizes muss ≤ Cap sein.
     *
     * - Standard (Mines, Storage, Producer, Smelter): 1
     * - Major Strategic (HUB, PROBE_LAB, RECYCLING_PLANT, TELESCOPE): 2
     * - Heavy-Industry (RESEARCH_LAB, SHIPYARD): 3
     */
    public function getSlotSize(): int
    {
        return match ($this) {
            self::RESEARCH_LAB,
            self::SHIPYARD => 3,
            self::HUB,
            self::PROBE_LAB,
            self::RECYCLING_PLANT,
            self::TELESCOPE => 2,
            default => 1,
        };
    }

    /**
     * Storage capacity contribution per Building-Level for a given resource (T-061).
     *
     * - Mining-Mines bringen kleinen Buffer für ihre eigene Resource
     * - IRON_SMELTER puffert IRON_BAR
     * - HUB bringt natural Renewable-Storage (Lebensraum-Boost)
     * - Dedizierte Storage-Buildings bringen viel
     */
    public function getStorageContribution(ResourceType $resource): int
    {
        $contributions = match ($this) {
            self::IRON_MINE => [ResourceType::IRON_ORE->value => 100],
            self::COAL_MINE => [ResourceType::COAL->value => 100],
            self::COPPER_MINE => [ResourceType::COPPER_ORE->value => 100],
            self::SILICON_MINE => [ResourceType::SILICON->value => 100],
            self::ALUMINUM_MINE => [ResourceType::ALUMINUM_ORE->value => 100],
            self::TITANIUM_MINE => [ResourceType::TITANIUM_ORE->value => 100],
            self::URANIUM_MINE => [ResourceType::URANIUM_ORE->value => 100],
            self::IRON_SMELTER => [ResourceType::IRON_BAR->value => 100],
            self::SHIPYARD => [],
            self::PROBE_LAB => [],
            self::RECYCLING_PLANT => [],
            self::TELESCOPE => [],
            self::RESEARCH_LAB => [],
            self::HUB => [
                ResourceType::WATER->value => 200,
                ResourceType::FOOD->value => 200,
                ResourceType::OXYGEN->value => 200,
            ],
            self::IRON_STORAGE => [ResourceType::IRON_ORE->value => 1000],
            self::COAL_STORAGE => [ResourceType::COAL->value => 1000],
            self::IRON_BAR_STORAGE => [ResourceType::IRON_BAR->value => 1000],
            self::WATER_TANK => [ResourceType::WATER->value => 2000],
            self::FOOD_SILO => [ResourceType::FOOD->value => 2000],
            self::OXYGEN_STORAGE => [ResourceType::OXYGEN->value => 2000],
            // T-097a: Producer haben kein Storage-Beitrag — bleibt bei Hub/Tank/Silo
            self::WATER_RECLAIMER => [],
            self::AGRI_DOME => [],
            self::ATMOSPHERIC_PROCESSOR => [],
        };

        return $contributions[$resource->value] ?? 0;
    }
}
