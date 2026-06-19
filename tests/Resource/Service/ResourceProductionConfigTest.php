<?php

declare(strict_types=1);

namespace App\Tests\Resource\Service;

use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceProductionConfigTest extends TestCase
{
    /**
     * @return array<string, array{ResourceType, float}>
     */
    public static function baseProductionProvider(): array
    {
        return [
            'iron'     => [ResourceType::IRON_ORE,     10.0],
            'coal'     => [ResourceType::COAL,         15.0],
            'copper'   => [ResourceType::COPPER_ORE,    8.0],
            'silicon'  => [ResourceType::SILICON,       6.0],
            'aluminum' => [ResourceType::ALUMINUM_ORE,  8.0],
            'titanium' => [ResourceType::TITANIUM_ORE,  4.0],
            'uranium'  => [ResourceType::URANIUM_ORE,   2.0],
            'water'    => [ResourceType::WATER,         5.0],
            'food'     => [ResourceType::FOOD,          3.0],
            'oxygen'   => [ResourceType::OXYGEN,        0.0],
        ];
    }

    #[DataProvider('baseProductionProvider')]
    public function test_base_production_value(ResourceType $type, float $expected): void
    {
        $config = new ResourceProductionConfig();

        self::assertSame($expected, $config->getBaseProduction($type));
    }
}

