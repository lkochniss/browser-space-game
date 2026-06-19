<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\ResourceBuildingMap;
use App\Building\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceBuildingMapTest extends TestCase
{
    /**
     * @return array<string, array{ResourceType, BuildingType}>
     */
    public static function finiteResourceMineProvider(): array
    {
        return [
            'iron'     => [ResourceType::IRON_ORE,      BuildingType::IRON_MINE],
            'coal'     => [ResourceType::COAL,          BuildingType::COAL_MINE],
            'copper'   => [ResourceType::COPPER_ORE,    BuildingType::COPPER_MINE],
            'silicon'  => [ResourceType::SILICON,       BuildingType::SILICON_MINE],
            'aluminum' => [ResourceType::ALUMINUM_ORE,  BuildingType::ALUMINUM_MINE],
            'titanium' => [ResourceType::TITANIUM_ORE,  BuildingType::TITANIUM_MINE],
            'uranium'  => [ResourceType::URANIUM_ORE,   BuildingType::URANIUM_MINE],
        ];
    }

    #[DataProvider('finiteResourceMineProvider')]
    public function test_each_finite_resource_has_dedicated_mine(ResourceType $resource, BuildingType $mine): void
    {
        $map = new ResourceBuildingMap();

        self::assertTrue($map->canProduce($mine, $resource));
        self::assertSame(1.0, $map->getMultiplier($resource, $mine));
        self::assertContains($mine->value, $map->getBuildingsForResource($resource));
    }

    public function test_renewables_have_no_mine_today(): void
    {
        $map = new ResourceBuildingMap();

        self::assertSame([], $map->getBuildingsForResource(ResourceType::WATER));
        self::assertSame([], $map->getBuildingsForResource(ResourceType::FOOD));
        self::assertSame([], $map->getBuildingsForResource(ResourceType::OXYGEN));
    }
}
