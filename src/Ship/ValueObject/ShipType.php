<?php

declare(strict_types=1);

namespace App\Ship\ValueObject;

/**
 * T-012 Foundation. T-014 (Kolo), T-015 (Transport), T-016 (Bergung) und
 * T-102 (Combat-Klassen) erweitern dieses Enum.
 */
enum ShipType: string
{
    case GENERIC = 'generic';
    case COLONY_SHIP = 'colony_ship';

    case TRANSPORT_SMALL = 'transport_small';
    case TRANSPORT_MEDIUM = 'transport_medium';
    case TRANSPORT_LARGE = 'transport_large';

    public function isTransport(): bool
    {
        return match ($this) {
            self::TRANSPORT_SMALL,
            self::TRANSPORT_MEDIUM,
            self::TRANSPORT_LARGE => true,
            default => false,
        };
    }
}
