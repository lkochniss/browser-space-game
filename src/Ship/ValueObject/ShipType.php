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

    case SALVAGE = 'salvage';

    public function isTransport(): bool
    {
        return match ($this) {
            self::TRANSPORT_SMALL,
            self::TRANSPORT_MEDIUM,
            self::TRANSPORT_LARGE => true,
            default => false,
        };
    }

    public function isSalvage(): bool
    {
        return $this === self::SALVAGE;
    }

    /**
     * T-017: Travel-Speed-Multiplier pro Schiffsklasse. Höher = schneller.
     * Fleet-Speed = min(getSpeed) aller Schiffe (langsamstes Schiff bestimmt).
     *
     * Werte sind Foundation-Tuning. T-026 Antriebs-Tech bringt später Boni.
     */
    public function getSpeed(): float
    {
        return match ($this) {
            self::GENERIC => 1.0,
            self::COLONY_SHIP => 0.7,        // träge wegen Settler-Cargo
            self::TRANSPORT_SMALL => 1.2,    // klein + schnell
            self::TRANSPORT_MEDIUM => 0.9,
            self::TRANSPORT_LARGE => 0.6,    // Heavy-Hauler
            self::SALVAGE => 0.8,            // mittelträge wegen Bergungs-Equipment
        };
    }

    /**
     * T-016 Salvage-Rate (Resource-Units pro Minute) bei aktivem Salvage-Action.
     * Nur SALVAGE-Schiffe können salvagen — andere returnen 0.
     *
     * Foundation-Wert: 50 Units/min = 3000/h. T-127 Mining/Industrie-Branch
     * kann später Rate-Boni bringen.
     */
    public function getSalvageRatePerMinute(): int
    {
        return match ($this) {
            self::SALVAGE => 50,
            default => 0,
        };
    }
}
