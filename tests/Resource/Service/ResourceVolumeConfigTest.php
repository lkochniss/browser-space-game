<?php

declare(strict_types=1);

namespace App\Tests\Resource\Service;

use App\Resource\Exception\UnknownResourceVolumeException;
use App\Resource\Service\ResourceVolumeConfig;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceVolumeConfigTest extends TestCase
{
    public function test_pop_multi_is_10(): void
    {
        self::assertSame(10.0, ResourceVolumeConfig::getPopMulti());
    }

    /**
     * @return array<string, array{0: ResourceType, 1: float}>
     */
    public static function resourceMultiProvider(): array
    {
        return [
            'water'         => [ResourceType::WATER, 1.0],
            'food'          => [ResourceType::FOOD, 1.2],
            'oxygen'        => [ResourceType::OXYGEN, 0.3],
            'iron_ore'      => [ResourceType::IRON_ORE, 2.0],
            'coal'          => [ResourceType::COAL, 1.8],
            'copper_ore'    => [ResourceType::COPPER_ORE, 2.0],
            'silicon'       => [ResourceType::SILICON, 1.8],
            'aluminum_ore'  => [ResourceType::ALUMINUM_ORE, 2.0],
            'titanium_ore'  => [ResourceType::TITANIUM_ORE, 2.0],
            'uranium_ore'   => [ResourceType::URANIUM_ORE, 2.5],
            'iron_bar'      => [ResourceType::IRON_BAR, 1.5],
            'debris_low'    => [ResourceType::DEBRIS_LOW, 1.0],
            'debris_medium' => [ResourceType::DEBRIS_MEDIUM, 1.0],
            'debris_high'   => [ResourceType::DEBRIS_HIGH, 1.0],
        ];
    }

    #[DataProvider('resourceMultiProvider')]
    public function test_resource_multi_per_type(ResourceType $type, float $expected): void
    {
        self::assertSame($expected, ResourceVolumeConfig::getMultiForResource($type));
    }

    public function test_all_existing_resource_types_have_volume_multi(): void
    {
        // Foundation: alle 14 aktuell existierenden ResourceTypes müssen abgedeckt sein.
        // Wenn ein neuer ResourceType hinzukommt + diesen Test bricht → Volume-Multi fehlt.
        foreach (ResourceType::cases() as $type) {
            $value = ResourceVolumeConfig::getMultiForResource($type);
            self::assertGreaterThan(0.0, $value, sprintf('Volume-Multi für %s muss > 0 sein', $type->value));
        }
    }

    public function test_all_returns_complete_map(): void
    {
        $map = ResourceVolumeConfig::all();
        self::assertCount(count(ResourceType::cases()), $map);
    }
}
