<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\RefinementRecipe;
use App\Resource\ValueObject\ResourceType;

class RefinementConfig
{
    /** @var array<string, RefinementRecipe> Map BuildingType.value → recipe */
    private array $recipesByBuilding;

    public function __construct()
    {
        $recipes = [
            new RefinementRecipe(
                output: ResourceType::IRON_BAR,
                outputAmount: 1,
                inputs: [
                    ResourceType::IRON_ORE->value => 2,
                    ResourceType::COAL->value => 1,
                ],
                building: BuildingType::IRON_SMELTER,
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
