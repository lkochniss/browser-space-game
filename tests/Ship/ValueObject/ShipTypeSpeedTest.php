<?php

declare(strict_types=1);

namespace App\Tests\Ship\ValueObject;

use App\Ship\ValueObject\ShipType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipTypeSpeedTest extends TestCase
{
    /**
     * @return array<string, array{ShipType, float}>
     */
    public static function speedProvider(): array
    {
        return [
            'generic'         => [ShipType::GENERIC,          1.0],
            'colony_ship'     => [ShipType::COLONY_SHIP,      0.7],
            'transport_small' => [ShipType::TRANSPORT_SMALL,  1.2],
            'transport_med'   => [ShipType::TRANSPORT_MEDIUM, 0.9],
            'transport_large' => [ShipType::TRANSPORT_LARGE,  0.6],
        ];
    }

    #[DataProvider('speedProvider')]
    public function test_speed_pro_typ(ShipType $type, float $expected): void
    {
        self::assertSame($expected, $type->getSpeed());
    }

    public function test_transport_small_is_fastest_transport(): void
    {
        self::assertGreaterThan(
            ShipType::TRANSPORT_LARGE->getSpeed(),
            ShipType::TRANSPORT_SMALL->getSpeed(),
        );
    }
}
