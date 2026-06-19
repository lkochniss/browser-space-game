<?php

declare(strict_types=1);

namespace App\Tests\Faction\ValueObject;

use App\Faction\ValueObject\ReputationTier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReputationTierTest extends TestCase
{
    /**
     * @return array<string, array{int, ReputationTier}>
     */
    public static function boundaryProvider(): array
    {
        return [
            'min'           => [-100, ReputationTier::HOSTILE],
            'hostile_high'  => [-30,  ReputationTier::HOSTILE],
            'neutral_low'   => [-29,  ReputationTier::NEUTRAL],
            'neutral_zero'  => [0,    ReputationTier::NEUTRAL],
            'neutral_high'  => [29,   ReputationTier::NEUTRAL],
            'friendly_low'  => [30,   ReputationTier::FRIENDLY],
            'friendly_high' => [69,   ReputationTier::FRIENDLY],
            'allied_low'    => [70,   ReputationTier::ALLIED],
            'max'           => [100,  ReputationTier::ALLIED],
        ];
    }

    #[DataProvider('boundaryProvider')]
    public function test_for_value_returns_expected_tier(int $value, ReputationTier $expected): void
    {
        self::assertSame($expected, ReputationTier::forValue($value));
    }
}
