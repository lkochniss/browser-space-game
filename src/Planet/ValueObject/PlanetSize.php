<?php

declare(strict_types=1);

namespace App\Planet\ValueObject;

enum PlanetSize: string
{
    case TINY = 'tiny';
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';
    case HUGE = 'huge';

    public function getDepositMultiplier(): float
    {
        return match ($this) {
            self::TINY => 0.5,
            self::SMALL => 0.75,
            self::MEDIUM => 1.0,
            self::LARGE => 1.5,
            self::HUGE => 2.0,
        };
    }
}
