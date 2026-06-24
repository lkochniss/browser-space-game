<?php

declare(strict_types=1);

namespace App\Ship\ValueObject;

/**
 * T-102 Combat-Schiff-Klassen. 5 Klassen × 3 Mark-Tiers = 15 Werte.
 *
 * Spezial-Schiffe (Colony/Transport/Salvage) sind weiter im `ShipType`-Enum
 * abgebildet — `Ship.shipClass` ist NULL für non-combat.
 *
 * Tier-Skalierung (Q1): Mk II = Mk I × 1.5 Stats × 3× Cost.
 * Mk III = Mk II × 1.5 × 3 → kumulativ Mk III ≈ 2.25× Mk I Stats, ≈ 9× Cost.
 */
enum ShipClass: string
{
    case FRIGATE_MK1 = 'frigate_mk1';
    case FRIGATE_MK2 = 'frigate_mk2';
    case FRIGATE_MK3 = 'frigate_mk3';

    case DESTROYER_MK1 = 'destroyer_mk1';
    case DESTROYER_MK2 = 'destroyer_mk2';
    case DESTROYER_MK3 = 'destroyer_mk3';

    case CRUISER_MK1 = 'cruiser_mk1';
    case CRUISER_MK2 = 'cruiser_mk2';
    case CRUISER_MK3 = 'cruiser_mk3';

    case BATTLESHIP_MK1 = 'battleship_mk1';
    case BATTLESHIP_MK2 = 'battleship_mk2';
    case BATTLESHIP_MK3 = 'battleship_mk3';

    case CARRIER_MK1 = 'carrier_mk1';
    case CARRIER_MK2 = 'carrier_mk2';
    case CARRIER_MK3 = 'carrier_mk3';

    public function getFamily(): string
    {
        return match ($this) {
            self::FRIGATE_MK1, self::FRIGATE_MK2, self::FRIGATE_MK3 => 'frigate',
            self::DESTROYER_MK1, self::DESTROYER_MK2, self::DESTROYER_MK3 => 'destroyer',
            self::CRUISER_MK1, self::CRUISER_MK2, self::CRUISER_MK3 => 'cruiser',
            self::BATTLESHIP_MK1, self::BATTLESHIP_MK2, self::BATTLESHIP_MK3 => 'battleship',
            self::CARRIER_MK1, self::CARRIER_MK2, self::CARRIER_MK3 => 'carrier',
        };
    }

    /** Mk-Tier 1..3. */
    public function getTier(): int
    {
        return match ($this) {
            self::FRIGATE_MK1, self::DESTROYER_MK1, self::CRUISER_MK1, self::BATTLESHIP_MK1, self::CARRIER_MK1 => 1,
            self::FRIGATE_MK2, self::DESTROYER_MK2, self::CRUISER_MK2, self::BATTLESHIP_MK2, self::CARRIER_MK2 => 2,
            self::FRIGATE_MK3, self::DESTROYER_MK3, self::CRUISER_MK3, self::BATTLESHIP_MK3, self::CARRIER_MK3 => 3,
        };
    }

    /**
     * T-102 Q5: Mark-spezifischer Research-Slug. Mk I = NULL (kein Gate);
     * Mk II/III brauchen `<family>_mk<tier>` Research-Node Lvl 1.
     */
    public function getRequiredResearchSlug(): ?string
    {
        if ($this->getTier() === 1) {
            return null;
        }

        return sprintf('%s_mk%d', $this->getFamily(), $this->getTier());
    }

    /**
     * T-102: Min-Shipyard-Level pro Familie (gleich für alle Mk-Tiers).
     */
    public function getRequiredShipyardLevel(): int
    {
        return match ($this->getFamily()) {
            'frigate' => 1,
            'destroyer' => 3,
            'cruiser' => 5,
            'battleship' => 8,
            'carrier' => 10,
        };
    }

    /**
     * T-102/T-104a: Escape-Pod-Survival-Chance bei Schiff-Loss in %. Wert ist
     * konstant je Familie (Mk-Tier verändert das nicht — Pod-Tech ist Lore-mäßig
     * Klassen-gebunden).
     */
    public function getEscapePodSurvivalChance(): int
    {
        return match ($this->getFamily()) {
            'frigate' => 30,
            'destroyer' => 50,
            'cruiser' => 65,
            'battleship' => 80,
            'carrier' => 70,
        };
    }

    public function requiresCaptain(): bool
    {
        return true;
    }
}
