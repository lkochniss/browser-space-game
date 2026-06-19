<?php

declare(strict_types=1);

namespace App\Tests\Planet\ValueObject;

use App\Planet\ValueObject\PlanetSize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanetSizeTest extends TestCase
{
    /**
     * @return array<string, array{PlanetSize, float}>
     */
    public static function multiplierProvider(): array
    {
        return [
            'tiny'   => [PlanetSize::TINY,   0.5],
            'small'  => [PlanetSize::SMALL,  0.75],
            'medium' => [PlanetSize::MEDIUM, 1.0],
            'large'  => [PlanetSize::LARGE,  1.5],
            'huge'   => [PlanetSize::HUGE,   2.0],
        ];
    }

    #[DataProvider('multiplierProvider')]
    public function test_deposit_multiplier(PlanetSize $size, float $expected): void
    {
        self::assertSame($expected, $size->getDepositMultiplier());
    }
}
