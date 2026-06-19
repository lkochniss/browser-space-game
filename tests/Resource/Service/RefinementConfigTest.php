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

    public function test_all_recipes_returns_iron_bar(): void
    {
        $config = new RefinementConfig();

        self::assertCount(1, $config->getAllRecipes());
        self::assertSame(ResourceType::IRON_BAR, $config->getAllRecipes()[0]->output);
    }
}
