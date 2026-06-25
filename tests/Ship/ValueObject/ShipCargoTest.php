<?php

declare(strict_types=1);

namespace App\Tests\Ship\ValueObject;

use App\Resource\ValueObject\ResourceType;
use App\Ship\ValueObject\ShipCargo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ShipCargoTest extends TestCase
{
    public function test_empty_cargo(): void
    {
        $cargo = ShipCargo::empty();

        self::assertTrue($cargo->isEmpty());
        self::assertSame(0, $cargo->getTotalUnits());
        self::assertSame(0, $cargo->usedVolume());
        self::assertSame(0, $cargo->getResource(ResourceType::IRON_BAR));
        self::assertSame(0, $cargo->getPopCount());
        self::assertSame([], $cargo->getResources());
    }

    public function test_used_volume_sums_resource_multi(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadResource(ResourceType::WATER, 50);     // 50 × 1.0 = 50
        $cargo->loadResource(ResourceType::IRON_BAR, 20);  // 20 × 1.5 = 30

        self::assertSame(80, $cargo->usedVolume());
        self::assertSame(70, $cargo->getTotalUnits());
    }

    public function test_used_volume_includes_pop_multi(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadPop(5);                                // 5 × 10 = 50

        self::assertSame(50, $cargo->usedVolume());
        self::assertSame(5, $cargo->getTotalUnits());
    }

    public function test_load_and_unload_resource(): void
    {
        $cargo = ShipCargo::empty();

        $cargo->loadResource(ResourceType::IRON_BAR, 100);
        $cargo->loadResource(ResourceType::COAL, 50);

        self::assertSame(100, $cargo->getResource(ResourceType::IRON_BAR));
        self::assertSame(50, $cargo->getResource(ResourceType::COAL));
        self::assertSame(150, $cargo->getTotalUnits());

        $cargo->unloadResource(ResourceType::IRON_BAR, 30);

        self::assertSame(70, $cargo->getResource(ResourceType::IRON_BAR));
        self::assertSame(120, $cargo->getTotalUnits());
    }

    public function test_unload_to_zero_removes_key(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadResource(ResourceType::IRON_BAR, 100);
        $cargo->unloadResource(ResourceType::IRON_BAR, 100);

        self::assertSame([], $cargo->getResources());
    }

    public function test_unload_more_than_loaded_throws(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadResource(ResourceType::IRON_BAR, 50);

        $this->expectException(InvalidArgumentException::class);
        $cargo->unloadResource(ResourceType::IRON_BAR, 60);
    }

    public function test_load_zero_or_negative_throws(): void
    {
        $cargo = ShipCargo::empty();

        $this->expectException(InvalidArgumentException::class);
        $cargo->loadResource(ResourceType::IRON_BAR, 0);
    }

    public function test_pop_load_unload(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadPop(20);
        $cargo->loadResource(ResourceType::IRON_BAR, 100);

        self::assertSame(20, $cargo->getPopCount());
        self::assertSame(120, $cargo->getTotalUnits());

        $cargo->unloadPop(10);
        self::assertSame(10, $cargo->getPopCount());
    }

    public function test_unload_more_pop_than_loaded_throws(): void
    {
        $cargo = ShipCargo::empty();
        $cargo->loadPop(10);

        $this->expectException(InvalidArgumentException::class);
        $cargo->unloadPop(20);
    }
}
