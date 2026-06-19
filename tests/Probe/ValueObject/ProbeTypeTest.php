<?php

declare(strict_types=1);

namespace App\Tests\Probe\ValueObject;

use App\Probe\ValueObject\ProbeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProbeTypeTest extends TestCase
{
    /**
     * @return array<string, array{ProbeType, int}>
     */
    public static function rangeProvider(): array
    {
        return [
            'system'    => [ProbeType::SYSTEM,    1],
            'orbital'   => [ProbeType::ORBITAL,   1],
            'deep_scan' => [ProbeType::DEEP_SCAN, 3],
        ];
    }

    #[DataProvider('rangeProvider')]
    public function test_range(ProbeType $type, int $expected): void
    {
        self::assertSame($expected, $type->getRange());
    }

    /**
     * @return array<string, array{ProbeType, bool}>
     */
    public static function oneShotProvider(): array
    {
        return [
            'system'    => [ProbeType::SYSTEM,    true],
            'orbital'   => [ProbeType::ORBITAL,   false],
            'deep_scan' => [ProbeType::DEEP_SCAN, true],
        ];
    }

    #[DataProvider('oneShotProvider')]
    public function test_is_one_shot(ProbeType $type, bool $expected): void
    {
        self::assertSame($expected, $type->isOneShot());
    }
}
