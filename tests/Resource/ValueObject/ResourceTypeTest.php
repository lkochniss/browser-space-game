<?php

declare(strict_types=1);

namespace App\Tests\Resource\ValueObject;

use App\Resource\ValueObject\ResourceCategory;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceTypeTest extends TestCase
{
    /**
     * @return array<string, array{ResourceType, ResourceCategory}>
     */
    public static function categoryProvider(): array
    {
        return [
            'iron'      => [ResourceType::IRON_ORE,      ResourceCategory::FINITE],
            'coal'      => [ResourceType::COAL,          ResourceCategory::FINITE],
            'copper'    => [ResourceType::COPPER_ORE,    ResourceCategory::FINITE],
            'silicon'   => [ResourceType::SILICON,       ResourceCategory::FINITE],
            'aluminum'  => [ResourceType::ALUMINUM_ORE,  ResourceCategory::FINITE],
            'titanium'  => [ResourceType::TITANIUM_ORE,  ResourceCategory::FINITE],
            'uranium'   => [ResourceType::URANIUM_ORE,   ResourceCategory::FINITE],
            'water'     => [ResourceType::WATER,         ResourceCategory::RENEWABLE],
            'food'      => [ResourceType::FOOD,          ResourceCategory::RENEWABLE],
            'oxygen'    => [ResourceType::OXYGEN,        ResourceCategory::RENEWABLE],
            'iron_bar'  => [ResourceType::IRON_BAR,      ResourceCategory::REFINED],
        ];
    }

    #[DataProvider('categoryProvider')]
    public function test_category(ResourceType $type, ResourceCategory $expected): void
    {
        self::assertSame($expected, $type->getCategory());
    }
}
