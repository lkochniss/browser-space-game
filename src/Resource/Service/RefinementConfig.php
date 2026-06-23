<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\RefinementRecipe;
use App\Resource\ValueObject\ResourceType;

/**
 * T-003 Foundation: IRON_SMELTER → IRON_BAR.
 * T-067: 8 Tier-2-Refineries (3 Bars + 5 Compounds) — siehe Recipes unten.
 *
 * Refinement-Tick-Pattern (T-067 Q3): Single-Step-pro-Tick — `RefinementProductionProcessor`
 * snapshotted REFINED-Input-Amounts vor dem Tick, so dass Cascade
 * (z.B. STEEL_SMELTER frisst STEEL aus IRON_SMELTER-Output desselben Ticks)
 * verhindert wird. Cascade läuft progressiv über mehrere Ticks.
 */
class RefinementConfig
{
    /** @var array<string, RefinementRecipe> Map BuildingType.value → recipe */
    private array $recipesByBuilding;

    public function __construct()
    {
        $recipes = [
            // T-003 Foundation
            new RefinementRecipe(
                output: ResourceType::IRON_BAR,
                outputAmount: 1,
                inputs: [
                    ResourceType::IRON_ORE->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::IRON_SMELTER,
            ),

            // T-067 Tier-2 Bars (Erz + Coal → Bar, 2:1 → 1)
            new RefinementRecipe(
                output: ResourceType::ALUMINUM_BAR,
                outputAmount: 1,
                inputs: [
                    ResourceType::ALUMINUM_ORE->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::ALUMINUM_REFINERY,
            ),
            new RefinementRecipe(
                output: ResourceType::COPPER_BAR,
                outputAmount: 1,
                inputs: [
                    ResourceType::COPPER_ORE->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::COPPER_REFINERY,
            ),
            new RefinementRecipe(
                output: ResourceType::TITANIUM_BAR,
                outputAmount: 1,
                inputs: [
                    ResourceType::TITANIUM_ORE->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::TITANIUM_REFINERY,
            ),

            // T-067 Tier-2 Compounds
            // STEEL = 2 IRON_BAR + 1 COAL → 1 STEEL
            // (Ticket-Vorschlag war 3:1 → 2; vereinfacht weil Processor `inputs`
            // als "per output unit" interpretiert. Effektiv 1.5:1 → 1 leicht
            // teurer in IRON_BAR; balance-OK.)
            new RefinementRecipe(
                output: ResourceType::STEEL,
                outputAmount: 1,
                inputs: [
                    ResourceType::IRON_BAR->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::STEEL_SMELTER,
            ),
            // CHIP = 2 COPPER_BAR + 1 SILICON → 1 CHIP
            new RefinementRecipe(
                output: ResourceType::CHIP,
                outputAmount: 1,
                inputs: [
                    ResourceType::COPPER_BAR->value => 2,
                    ResourceType::SILICON->value => 1,
                ],
                building: BuildingType::CHIP_FAB,
            ),
            // COMPOSITE = 2 ALUMINUM_BAR + 2 PLASTIC_RESIN → 1 COMPOSITE
            new RefinementRecipe(
                output: ResourceType::COMPOSITE,
                outputAmount: 1,
                inputs: [
                    ResourceType::ALUMINUM_BAR->value => 2,
                    ResourceType::PLASTIC_RESIN->value => 2,
                ],
                building: BuildingType::COMPOSITE_PLANT,
            ),
            // HULL_PLATE = 4 STEEL + 2 COMPOSITE → 1 HULL_PLATE
            new RefinementRecipe(
                output: ResourceType::HULL_PLATE,
                outputAmount: 1,
                inputs: [
                    ResourceType::STEEL->value => 4,
                    ResourceType::COMPOSITE->value => 2,
                ],
                building: BuildingType::HULL_FOUNDRY,
            ),
            // SHIELD_MODULE = 3 CHIP + 1 TRITIUM_ORE → 1 SHIELD_MODULE
            new RefinementRecipe(
                output: ResourceType::SHIELD_MODULE,
                outputAmount: 1,
                inputs: [
                    ResourceType::CHIP->value => 3,
                    ResourceType::TRITIUM_ORE->value => 1,
                ],
                building: BuildingType::SHIELD_ASSEMBLER,
            ),
        ];

        $this->recipesByBuilding = [];
        foreach ($recipes as $recipe) {
            $this->recipesByBuilding[$recipe->building->value] = $recipe;
        }
    }

    public function getRecipeForBuilding(BuildingType $type): ?RefinementRecipe
    {
        return $this->recipesByBuilding[$type->value] ?? null;
    }

    /** @return RefinementRecipe[] */
    public function getAllRecipes(): array
    {
        return array_values($this->recipesByBuilding);
    }
}
