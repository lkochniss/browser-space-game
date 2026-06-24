<?php

declare(strict_types=1);

namespace App\Building\Service;

use App\Building\ValueObject\BuildingCost;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\SoftCapConfig;
use App\Resource\ValueObject\ResourceType;
use LogicException;

class BuildingCostConfig
{
    /** @var array<string, BuildingCost> */
    private array $costs;

    public function __construct(
        private readonly SoftCapConfig $softCap = new SoftCapConfig(),
    ) {
        $this->costs = [
            BuildingType::IRON_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 50],
                populationCost: 5,
            ),
            BuildingType::COAL_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 30],
                populationCost: 5,
            ),
            BuildingType::COPPER_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 60],
                populationCost: 5,
            ),
            BuildingType::SILICON_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 80],
                populationCost: 5,
            ),
            BuildingType::ALUMINUM_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 80],
                populationCost: 5,
            ),
            BuildingType::TITANIUM_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 100],
                populationCost: 5,
            ),
            BuildingType::URANIUM_MINE->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 100,
                    ResourceType::COAL->value => 30,
                ],
                populationCost: 10,
            ),
            // T-172: HQ (Heavy-Verwaltung) — strikt-unique, auto-built bei Claim.
            BuildingType::HQ->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 200,
                    ResourceType::COAL->value => 100,
                ],
                populationCost: 20,
            ),
            // T-172: HUB (Wohnsiedlung) — multi-instance, klein.
            BuildingType::HUB->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 50,
                    ResourceType::COAL->value => 25,
                ],
                populationCost: 5,
            ),
            BuildingType::IRON_SMELTER->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 200,
                    ResourceType::COAL->value => 100,
                ],
                populationCost: 15,
            ),

            // T-011: Raumwerft. Strategic-Tier, Voraussetzung für Schiffsbau (T-012ff).
            BuildingType::SHIPYARD->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 500,
                    ResourceType::COAL->value => 100,
                    ResourceType::ALUMINUM_ORE->value => 200,
                    ResourceType::TITANIUM_ORE->value => 50,
                ],
                populationCost: 30,
            ),

            // T-013: Probe-Lab. Voraussetzung für Sondenbau (T-013ff).
            BuildingType::PROBE_LAB->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 200,
                    ResourceType::SILICON->value => 100,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                populationCost: 15,
            ),

            // T-021: Recycling-Plant. Konsumiert DEBRIS_* aus Planet-Storage und produziert
            // zufällige FINITE/REFINED-Outputs pro Tick.
            BuildingType::RECYCLING_PLANT->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 250,
                    ResourceType::COPPER_ORE->value => 100,
                    ResourceType::SILICON->value => 80,
                ],
                populationCost: 10,
            ),

            // T-018: Teleskop. Deckt SolarSystems pro Tick auf.
            BuildingType::TELESCOPE->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 150,
                    ResourceType::SILICON->value => 200,
                    ResourceType::COPPER_ORE->value => 100,
                ],
                populationCost: 10,
            ),

            // T-025: Research-Lab. Voraussetzung für Forschung; reduziert Duration.
            BuildingType::RESEARCH_LAB->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 200,
                    ResourceType::SILICON->value => 100,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                populationCost: 15,
            ),

            // T-177: WAREHOUSE konsolidiert T-061 (6 Storage-Buildings gelöscht).
            // Hauptquelle Volume-Storage; non-unique, 1 Slot, +500 m³/Lvl.
            BuildingType::WAREHOUSE->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 100,
                    ResourceType::COAL->value => 50,
                ],
                populationCost: 5,
            ),

            // T-097a: Renewable-Producer (Tier-0)
            BuildingType::WATER_RECLAIMER->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 100],
                populationCost: 5,
            ),
            BuildingType::AGRI_DOME->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 100],
                populationCost: 5,
            ),
            BuildingType::ATMOSPHERIC_PROCESSOR->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 100],
                populationCost: 5,
            ),

            // T-064b → T-172 Rename: Construction-Yard. Strikt-unique, Tier-1 via metallurgy.
            BuildingType::CONSTRUCTION_YARD->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 200,
                    ResourceType::SILICON->value => 100,
                ],
                populationCost: 10,
            ),

            // T-070: Pop-QoL-Buildings (alle strikt-unique pro Planet)
            // T-182: UNIVERSITY entfernt (Wort-Mix-Up mit RESEARCH_LAB).
            BuildingType::HOSPITAL->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 250,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                populationCost: 30,
            ),
            BuildingType::CULTURAL_CENTER->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 200,
                    ResourceType::SILICON->value => 50,
                ],
                populationCost: 20,
            ),
            BuildingType::TEMPLE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 150],
                populationCost: 15,
            ),

            // T-067 Tier-2 Mines (FINITE Erze)
            BuildingType::PLASTIC_RESIN_MINE->value => new BuildingCost(
                resources: [ResourceType::IRON_ORE->value => 80],
                populationCost: 5,
            ),
            BuildingType::TRITIUM_MINE->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_ORE->value => 100,
                    ResourceType::COAL->value => 30,
                ],
                populationCost: 10,
            ),

            // T-067 Tier-2 Refineries (Bars — günstig, Foundation für Compounds)
            BuildingType::ALUMINUM_REFINERY->value => new BuildingCost(
                resources: [ResourceType::IRON_BAR->value => 100],
                populationCost: 10,
            ),
            BuildingType::COPPER_REFINERY->value => new BuildingCost(
                resources: [ResourceType::IRON_BAR->value => 100],
                populationCost: 10,
            ),
            BuildingType::TITANIUM_REFINERY->value => new BuildingCost(
                resources: [ResourceType::IRON_BAR->value => 150],
                populationCost: 15,
            ),

            // T-067 Tier-2 Refineries (Compounds — schwerer, hochwertig)
            BuildingType::STEEL_SMELTER->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 200,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                populationCost: 20,
            ),
            BuildingType::CHIP_FAB->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 150,
                    ResourceType::SILICON->value => 100,
                ],
                populationCost: 20,
            ),
            BuildingType::COMPOSITE_PLANT->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 200,
                    ResourceType::ALUMINUM_ORE->value => 50,
                ],
                populationCost: 20,
            ),
            BuildingType::HULL_FOUNDRY->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 300,
                    ResourceType::TITANIUM_ORE->value => 100,
                ],
                populationCost: 30,
            ),
            BuildingType::SHIELD_ASSEMBLER->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 250,
                    ResourceType::SILICON->value => 100,
                ],
                populationCost: 25,
            ),

            // T-104a Crew-Foundation
            BuildingType::ACADEMY->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 300,
                    ResourceType::SILICON->value => 80,
                ],
                populationCost: 30,
            ),
            BuildingType::OFFICER_QUARTERS->value => new BuildingCost(
                resources: [
                    ResourceType::IRON_BAR->value => 200,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                populationCost: 20,
            ),
        ];
    }

    /**
     * Returns the cost to construct or upgrade a building.
     *
     * - $currentLevel = 0 → initial build cost (base, multiplier 2^0 = 1).
     * - $currentLevel = N → cost to upgrade from level N to N+1 (base × 2^N).
     * - T-151: Ab Level 20+ kommt zusätzlich Soft-Cap-Multiplier 1.05^(lvl-20)
     *   on top des base-Doublers.
     */
    public function getCost(BuildingType $type, int $currentLevel = 0): BuildingCost
    {
        if (!isset($this->costs[$type->value])) {
            throw new LogicException(sprintf('No cost configured for building type "%s"', $type->value));
        }
        if ($currentLevel < 0) {
            throw new LogicException(sprintf('currentLevel must be >= 0, got %d', $currentLevel));
        }

        $base = $this->costs[$type->value];
        $multiplier = (2 ** $currentLevel) * $this->softCap->buildingCostMultiplier($currentLevel);

        $scaledResources = [];
        foreach ($base->resources as $resourceTypeValue => $amount) {
            $scaledResources[$resourceTypeValue] = (int) ceil($amount * $multiplier);
        }

        return new BuildingCost(
            resources: $scaledResources,
            populationCost: (int) ceil($base->populationCost * $multiplier),
        );
    }
}
