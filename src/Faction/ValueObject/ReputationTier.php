<?php

declare(strict_types=1);

namespace App\Faction\ValueObject;

enum ReputationTier: string
{
    case HOSTILE = 'hostile';
    case NEUTRAL = 'neutral';
    case FRIENDLY = 'friendly';
    case ALLIED = 'allied';

    /**
     * Tier-Bands: HOSTILE [-100..-30], NEUTRAL [-29..29], FRIENDLY [30..69], ALLIED [70..100]
     */
    public static function forValue(int $value): self
    {
        return match (true) {
            $value <= -30 => self::HOSTILE,
            $value <= 29 => self::NEUTRAL,
            $value <= 69 => self::FRIENDLY,
            default => self::ALLIED,
        };
    }
}
