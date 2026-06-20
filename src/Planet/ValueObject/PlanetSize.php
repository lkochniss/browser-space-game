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

    /**
     * T-171: Building-Slot-Cap pro Planet-Size. Summe aller Building-getSlotSize()
     * darf nicht über diesem Wert liegen. Spieler entscheidet Spezialisierung
     * (z.B. reine Aggrarwelt mit vielen Producer-Slots, keine Mines).
     */
    public function getBuildingSlotCap(): int
    {
        return match ($this) {
            self::TINY => 8,
            self::SMALL => 12,
            self::MEDIUM => 18,
            self::LARGE => 28,
            self::HUGE => 40,
        };
    }
}
