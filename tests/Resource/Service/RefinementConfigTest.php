<?php

declare(strict_types=1);

namespace App\Tests\Resource\Service;

use App\Building\ValueObject\BuildingType;
use App\Resource\Service\RefinementConfig;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\TestCase;

final class RefinementConfigTest extends TestCase
{
    public function test_iron_smelter_has_iron_bar_recipe(): void
    {
        $config = new RefinementConfig();
        $recipe = $config->getRecipeForBuilding(BuildingType::IRON_SMELTER);

        self::assertNotNull($recipe);
        self::assertSame(ResourceType::IRON_BAR, $recipe->output);
        self::assertSame(1, $recipe->outputAmount);
        self::assertSame(2, $recipe->inputs[ResourceType::IRON_ORE->value]);
        self::assertSame(1, $recipe->inputs[ResourceType::COAL->value]);
    }

    public function test_unmapped_building_returns_null(): void
    {
        $config = new RefinementConfig();

        self::assertNull($config->getRecipeForBuilding(BuildingType::IRON_MINE));
        self::assertNull($config->getRecipeForBuilding(BuildingType::HUB));
    }

    public function test_all_recipes_includes_t003_and_t067(): void
    {
        // T-003 (1) + T-067 (8) = 9 Recipes
        $config = new RefinementConfig();

        self::assertCount(9, $config->getAllRecipes());

        $outputs = array_map(static fn ($r) => $r->output, $config->getAllRecipes());
        self::assertContains(ResourceType::IRON_BAR, $outputs);
        // T-067 Bars
        self::assertContains(ResourceType::ALUMINUM_BAR, $outputs);
        self::assertContains(ResourceType::COPPER_BAR, $outputs);
        self::assertContains(ResourceType::TITANIUM_BAR, $outputs);
        // T-067 Compounds
        self::assertContains(ResourceType::STEEL, $outputs);
        self::assertContains(ResourceType::CHIP, $outputs);
        self::assertContains(ResourceType::COMPOSITE, $outputs);
        self::assertContains(ResourceType::HULL_PLATE, $outputs);
        self::assertContains(ResourceType::SHIELD_MODULE, $outputs);
    }

    public function test_steel_recipe_2_iron_bar_plus_1_coal_yields_1_steel(): void
    {
        // T-067: STEEL = 2 IRON_BAR + 1 COAL → 1 STEEL (vereinfacht aus 3:1→2,
        // weil RefinementProcessor inputs als "per output unit" interpretiert)
        $config = new RefinementConfig();
        $recipe = $config->getRecipeForBuilding(BuildingType::STEEL_SMELTER);

        self::assertNotNull($recipe);
        self::assertSame(ResourceType::STEEL, $recipe->output);
        self::assertSame(1, $recipe->outputAmount);
        self::assertSame(2, $recipe->inputs[ResourceType::IRON_BAR->value]);
        self::assertSame(1, $recipe->inputs[ResourceType::COAL->value]);
    }
}
