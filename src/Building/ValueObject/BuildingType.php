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

    // T-172: HQ ist die zentrale Planet-Verwaltung — strikt-unique, gibt Pop-Cap-
    // Foundation + Slot-Bonus (PlanetSize-abhängig, capped) + Trade-Output-Hook.
    // Wird beim ClaimStartPlanet automatisch als L1 errichtet.
    case HQ = 'hq';

    // T-172: HUB ist Wohnsiedlung — non-unique, multi-instance Pop-Cap-Booster
    // ohne Storage-Beitrag. Storage kommt aus Tank/Silo/Storage-Buildings.
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

    // T-064b → T-172 rename: Lokaler Bauzeit-Boost. Unique pro Planet, Tier-1.
    case CONSTRUCTION_YARD = 'construction_yard';

    public function getPopulationCapBonusPerLevel(): int
    {
        return match ($this) {
            // T-172: HQ gibt kleine Pop-Cap-Foundation (+25/Level).
            // HUB ist Wohnsiedlung (+100/Level, multi-stackable).
            self::HQ => 25,
            self::HUB => 100,
            default => 0,
        };
    }

    /**
     * T-171: Strikt-unique Buildings — max 1 Instanz pro Planet, Folge-Build wirft
     * `BuildingAlreadyExistsException`. Spieler nutzt Upgrade um Level zu steigern.
     *
     * Strategic + Lifelines sind unique; Mines/Storage/Producer/Smelter/HUB sind Multi.
     * T-172: HQ ist unique, HUB ist multi (vorher beide HUB+unique).
     */
    public function isUnique(): bool
    {
        return match ($this) {
            self::HQ,
            self::RESEARCH_LAB,
            self::SHIPYARD,
            self::PROBE_LAB,
            self::RECYCLING_PLANT,
            self::TELESCOPE,
            self::CONSTRUCTION_YARD => true,
            default => false,
        };
    }

    /**
     * T-171: Slot-Size pro Building. Planet hat Slot-Cap (PlanetSize-abhängig);
     * Summe der gebauten + in-Bau-Building-Sizes muss ≤ Cap sein.
     *
     * - Standard (Mines, Storage, Producer, Smelter, HUB-neu): 1
     * - Major Strategic (PROBE_LAB, RECYCLING_PLANT, TELESCOPE, CONSTRUCTION_YARD): 2
     * - Heavy-Industry / Verwaltung (HQ, RESEARCH_LAB, SHIPYARD): 3
     */
    public function getSlotSize(): int
    {
        return match ($this) {
            self::HQ,
            self::RESEARCH_LAB,
            self::SHIPYARD => 3,
            self::PROBE_LAB,
            self::RECYCLING_PLANT,
            self::TELESCOPE,
            self::CONSTRUCTION_YARD => 2,
            default => 1,
        };
    }

    /**
     * Storage capacity contribution per Building-Level for a given resource (T-061).
     *
     * - Mining-Mines bringen kleinen Buffer für ihre eigene Resource
     * - IRON_SMELTER puffert IRON_BAR
     * - HQ bringt natural Renewable-Storage (Lebensraum-Foundation, T-172)
     * - HUB hat KEIN Storage (T-172 Decision)
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
            // T-172: HQ übernimmt Renewable-Storage-Foundation (vom alten HUB).
            self::HQ => [
                ResourceType::WATER->value => 200,
                ResourceType::FOOD->value => 200,
                ResourceType::OXYGEN->value => 200,
            ],
            // T-172: HUB hat kein Storage-Beitrag (reines Wohngebäude).
            self::HUB => [],
            self::IRON_STORAGE => [ResourceType::IRON_ORE->value => 1000],
            self::COAL_STORAGE => [ResourceType::COAL->value => 1000],
            self::IRON_BAR_STORAGE => [ResourceType::IRON_BAR->value => 1000],
            self::WATER_TANK => [ResourceType::WATER->value => 2000],
            self::FOOD_SILO => [ResourceType::FOOD->value => 2000],
            self::OXYGEN_STORAGE => [ResourceType::OXYGEN->value => 2000],
            // T-097a: Producer haben kein Storage-Beitrag
            self::WATER_RECLAIMER => [],
            self::AGRI_DOME => [],
            self::ATMOSPHERIC_PROCESSOR => [],
            self::CONSTRUCTION_YARD => [],
        };

        return $contributions[$resource->value] ?? 0;
    }
}
