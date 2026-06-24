<?php

declare(strict_types=1);

namespace App\Building\ValueObject;

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
    // T-177: HQ trägt 25 m³/Lvl zum Generic-Storage bei (Verwaltungs-Buffer).
    case HQ = 'hq';

    // T-172: HUB ist Wohnsiedlung — non-unique, multi-instance Pop-Cap-Booster
    // ohne Storage-Beitrag.
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

    // T-177: WAREHOUSE konsolidiert die 6 T-061-Storage-Buildings (IRON_STORAGE /
    // COAL_STORAGE / IRON_BAR_STORAGE / WATER_TANK / FOOD_SILO / OXYGEN_STORAGE
    // wurden gelöscht). Generisches Volume-Lager: +500 m³/Lvl, non-unique.
    case WAREHOUSE = 'warehouse';

    // T-097a: Renewable-Producer. Tier-0, ohne sie kein Pop-Survival.
    case WATER_RECLAIMER = 'water_reclaimer';
    case AGRI_DOME = 'agri_dome';
    case ATMOSPHERIC_PROCESSOR = 'atmospheric_processor';

    // T-064b → T-172 rename: Lokaler Bauzeit-Boost. Unique pro Planet, Tier-1.
    case CONSTRUCTION_YARD = 'construction_yard';

    // T-070 Pop-QoL-Buildings: Quality-of-Life-Strukturen für Pop. Alle
    // strikt-unique pro Planet, Slot-1.
    case HOSPITAL = 'hospital';
    case CULTURAL_CENTER = 'cultural_center';
    case TEMPLE = 'temple';

    // T-067 Tier-2 Erzeugnis-Tree:
    //  - 2 neue Mines für FINITE Erze (PLASTIC_RESIN, TRITIUM_ORE)
    //  - 8 Refineries: 3 Bar (Aluminum/Copper/Titanium) + 5 Compounds (Steel/Chip/Composite/Hull/Shield)
    case PLASTIC_RESIN_MINE = 'plastic_resin_mine';
    case TRITIUM_MINE = 'tritium_mine';

    case ALUMINUM_REFINERY = 'aluminum_refinery';
    case COPPER_REFINERY = 'copper_refinery';
    case TITANIUM_REFINERY = 'titanium_refinery';
    case STEEL_SMELTER = 'steel_smelter';
    case CHIP_FAB = 'chip_fab';
    case COMPOSITE_PLANT = 'composite_plant';
    case HULL_FOUNDRY = 'hull_foundry';
    case SHIELD_ASSEMBLER = 'shield_assembler';

    // T-104a Crew-Foundation:
    //  - ACADEMY trainiert Crew (Captains, später Engineer/Diplomat via T-104c)
    //  - OFFICER_QUARTERS bietet Wohnraum-Cap (max 5 Crew/Level pro Instance)
    // Beide non-unique, Slot-Size 2 (heavy-Infrastructure).
    case ACADEMY = 'academy';
    case OFFICER_QUARTERS = 'officer_quarters';

    // T-068 Defense-Buildings (Planet-Defense gegen NPC/Pirate-Angriffe).
    // PLANETARY_SHIELD + SENSOR_ARRAY = strikt-unique (Slot-Size 2);
    // DEFENSE_TURRET + AA_BATTERY = non-unique (Slot-Size 1).
    case PLANETARY_SHIELD = 'planetary_shield';
    case DEFENSE_TURRET = 'defense_turret';
    case SENSOR_ARRAY = 'sensor_array';
    case AA_BATTERY = 'aa_battery';

    public function getPopulationCapBonusPerLevel(): int
    {
        return match ($this) {
            self::HQ => 25,
            self::HUB => 100,
            self::HOSPITAL => 20,
            default => 0,
        };
    }

    /**
     * T-171: Strikt-unique Buildings — max 1 Instanz pro Planet, Folge-Build wirft
     * `BuildingAlreadyExistsException`. Spieler nutzt Upgrade um Level zu steigern.
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
            self::CONSTRUCTION_YARD,
            self::HOSPITAL,
            self::CULTURAL_CENTER,
            self::TEMPLE,
            self::PLANETARY_SHIELD,
            self::SENSOR_ARRAY => true,
            default => false,
        };
    }

    public function getSlotSize(): int
    {
        return match ($this) {
            self::HQ,
            self::RESEARCH_LAB,
            self::SHIPYARD => 3,
            self::PROBE_LAB,
            self::RECYCLING_PLANT,
            self::TELESCOPE,
            self::CONSTRUCTION_YARD,
            self::ACADEMY,
            self::OFFICER_QUARTERS,
            self::PLANETARY_SHIELD,
            self::SENSOR_ARRAY => 2,
            default => 1,
        };
    }

    /**
     * T-065 Power-Production pro Building (gegeben Level).
     * Foundation: nur HUB ist Bootstrap-Producer (`50 + 25 × level` pro Instance).
     * T-071 erweitert um dedizierte Power-Plants (Solar/Fusion/Antimatter).
     */
    public function getPowerProduction(int $level): int
    {
        return match ($this) {
            self::HUB => 50 + 25 * $level,
            default => 0,
        };
    }

    /**
     * T-065 Power-Consumption pro Building (gegeben Level). Werte laut
     * Decision-Tabelle T-065 (Q2 sanft). Tuning-Knob für späteres Balancing.
     */
    public function getPowerConsumption(int $level): int
    {
        return match ($this) {
            self::HQ,
            self::HUB,
            self::WAREHOUSE,
            self::WATER_RECLAIMER,
            self::AGRI_DOME,
            self::ATMOSPHERIC_PROCESSOR => 1 * $level,
            self::IRON_MINE,
            self::COAL_MINE,
            self::COPPER_MINE,
            self::SILICON_MINE,
            self::ALUMINUM_MINE,
            self::TITANIUM_MINE,
            self::URANIUM_MINE,
            self::PLASTIC_RESIN_MINE,
            self::TRITIUM_MINE => 3 * $level,
            self::HOSPITAL,
            self::CULTURAL_CENTER,
            self::TEMPLE => 5 * $level,
            self::RECYCLING_PLANT => 6 * $level,
            self::IRON_SMELTER,
            self::ALUMINUM_REFINERY,
            self::COPPER_REFINERY,
            self::TITANIUM_REFINERY,
            self::STEEL_SMELTER,
            self::CHIP_FAB,
            self::COMPOSITE_PLANT,
            self::HULL_FOUNDRY,
            self::SHIELD_ASSEMBLER,
            self::CONSTRUCTION_YARD => 8 * $level,
            self::RESEARCH_LAB,
            self::PROBE_LAB,
            self::TELESCOPE => 10 * $level,
            self::SHIPYARD => 15 * $level,
            // T-068 Defense — heavy-Consumer (Late-Game-Bottleneck).
            self::PLANETARY_SHIELD => 100 * $level,
            self::AA_BATTERY => 40 * $level,
            self::DEFENSE_TURRET => 30 * $level,
            self::SENSOR_ARRAY => 20 * $level,
            default => 0,
        };
    }

    /**
     * T-068 Defense-Buildings haben HP. Max-HP skaliert linear mit Level.
     * Non-Defense-Types liefern 0 (kein HP-Tracking nötig).
     */
    public function getMaxHpPerLevel(): int
    {
        return match ($this) {
            self::PLANETARY_SHIELD => 100,
            self::DEFENSE_TURRET => 200,
            self::SENSOR_ARRAY => 100,
            self::AA_BATTERY => 150,
            default => 0,
        };
    }

    /**
     * T-068 PLANETARY_SHIELD-Stat: zusätzliche Shield-HP pro Level zur
     * Planet-Defense (über `Planet::getDefenseStats`).
     */
    public function getDefenseShieldHpPerLevel(): int
    {
        return $this === self::PLANETARY_SHIELD ? 5000 : 0;
    }

    /** T-068 Turret-Damage pro Level. */
    public function getDefenseTurretDamagePerLevel(): int
    {
        return $this === self::DEFENSE_TURRET ? 500 : 0;
    }

    /** T-068 AA-Damage pro Level. */
    public function getDefenseAaDamagePerLevel(): int
    {
        return $this === self::AA_BATTERY ? 300 : 0;
    }

    /** T-068 Sensor-Range (Systeme) pro Level. */
    public function getSensorRangePerLevel(): int
    {
        return $this === self::SENSOR_ARRAY ? 1 : 0;
    }

    public function isDefenseBuilding(): bool
    {
        return match ($this) {
            self::PLANETARY_SHIELD,
            self::DEFENSE_TURRET,
            self::SENSOR_ARRAY,
            self::AA_BATTERY => true,
            default => false,
        };
    }

    /**
     * T-177: Volume-Beitrag pro Building-Level zum generischen Planet-Storage.
     *
     * Ersetzt den alten `getStorageContribution(ResourceType)` (T-061 per-Resource-
     * Cap). Generisches Lager in m³ — alle Items belegen Volume × Multi via
     * `ResourceVolumeConfig`.
     *
     * Werte (T-177 Q2-Decision):
     * - HQ: 25 m³/Lvl (Verwaltungs-Buffer; mit Base 50 m³ → HQ L1 = 75 m³)
     * - WAREHOUSE: 500 m³/Lvl (Hauptquelle Volume)
     * - Mines / Refineries: 50 m³/Lvl (kleiner Buffer für eigene Output)
     * - Recycling-Plant: 100 m³/Lvl (voluminöses Debris)
     * - HUB / QoL / Strategic: 0
     */
    public function getVolumeContribution(): int
    {
        return match ($this) {
            self::HQ => 25,
            self::WAREHOUSE => 500,
            self::IRON_MINE,
            self::COAL_MINE,
            self::COPPER_MINE,
            self::SILICON_MINE,
            self::ALUMINUM_MINE,
            self::TITANIUM_MINE,
            self::URANIUM_MINE,
            self::PLASTIC_RESIN_MINE,
            self::TRITIUM_MINE,
            self::IRON_SMELTER,
            self::ALUMINUM_REFINERY,
            self::COPPER_REFINERY,
            self::TITANIUM_REFINERY,
            self::STEEL_SMELTER,
            self::CHIP_FAB,
            self::COMPOSITE_PLANT,
            self::HULL_FOUNDRY,
            self::SHIELD_ASSEMBLER => 50,
            self::RECYCLING_PLANT => 100,
            default => 0,
        };
    }
}

