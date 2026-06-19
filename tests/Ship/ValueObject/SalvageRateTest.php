<?php

declare(strict_types=1);

namespace App\Tests\Ship\ValueObject;

use App\Ship\ValueObject\ShipType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SalvageRateTest extends TestCase
{
    public function test_salvage_ship_has_rate(): void
    {
        self::assertSame(50, ShipType::SALVAGE->getSalvageRatePerMinute());
        self::assertTrue(ShipType::SALVAGE->isSalvage());
    }

    /**
     * @return array<string, array{ShipType}>
     */
    public static function nonSalvageProvider(): array
    {
        return [
            'generic'         => [ShipType::GENERIC],
            'colony_ship'     => [ShipType::COLONY_SHIP],
            'transport_small' => [ShipType::TRANSPORT_SMALL],
            'transport_med'   => [ShipType::TRANSPORT_MEDIUM],
            'transport_large' => [ShipType::TRANSPORT_LARGE],
        ];
    }

    #[DataProvider('nonSalvageProvider')]
    public function test_non_salvage_ship_has_zero_rate(ShipType $type): void
    {
        self::assertSame(0, $type->getSalvageRatePerMinute());
        self::assertFalse($type->isSalvage());
    }
}
