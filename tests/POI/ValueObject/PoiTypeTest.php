<?php

declare(strict_types=1);

namespace App\Tests\POI\ValueObject;

use App\POI\ValueObject\PoiType;
use PHPUnit\Framework\TestCase;

final class PoiTypeTest extends TestCase
{
    public function test_all_seven_subtypes_are_defined(): void
    {
        $expected = [
            'debris_field',
            'nebula',
            'station',
            'unknown_fleet',
            'asteroid_field',
            'wormhole',
            'black_hole',
        ];

        $actual = array_map(static fn (PoiType $t) => $t->value, PoiType::cases());
        sort($expected);
        sort($actual);

        self::assertSame($expected, $actual);
    }
}
