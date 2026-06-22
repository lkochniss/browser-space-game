<?php

declare(strict_types=1);

namespace App\Ship\ValueObject;

/**
 * T-026c: Antriebs-Typ pro Ship. Mapped 1:1 auf die T-026 Antriebs-Forschungs-
 * Nodes (HYDROGEN ist Foundation-Default, alle anderen brauchen Research).
 *
 * - `getSpeedMultiplier()` skaliert `ShipType::getSpeed()` (T-017
 *   Fleet-Speed-Mechanik) — höher = schneller.
 * - `getMaxSystemRange()` bezeichnet die maximale Anzahl System-Sprünge pro
 *   Bewegung (0 = kein FTL). Informativ; Enforcement folgt in T-026d.
 * - `getRequiredResearchSlug()` = Forschung, die Player haben muss um ein
 *   Schiff mit diesem Antrieb zu bauen (NULL für HYDROGEN-Foundation).
 *
 * Out-of-Scope: Fuel-Mechanik (T-066), Refit auf bestehenden Schiffen.
 */
enum PropulsionType: string
{
    case HYDROGEN = 'hydrogen';
    case ION = 'ion';
    case FUSION = 'fusion';
    case ANTIMATTER = 'antimatter';
    case HYPERDRIVE = 'hyperdrive';
    case WARP = 'warp';
    case JUMPDRIVE = 'jumpdrive';

    /**
     * In-System-Speed-Multiplier relativ zu HYDROGEN (Baseline 1.0).
     * Wird mit `ShipType::getSpeed()` multipliziert.
     */
    public function getSpeedMultiplier(): float
    {
        return match ($this) {
            self::HYDROGEN => 1.0,
            self::ION => 1.3,
            self::FUSION => 1.7,
            self::ANTIMATTER => 2.2,
            self::HYPERDRIVE => 1.5,
            self::WARP => 2.0,
            self::JUMPDRIVE => 2.5,
        };
    }

    /**
     * Max System-Sprünge pro Move. 0 = kein FTL (Inter-System gesperrt).
     * Standard-Antriebe (HYDROGEN..ANTIMATTER) sind In-System-only.
     */
    public function getMaxSystemRange(): int
    {
        return match ($this) {
            self::HYDROGEN, self::ION, self::FUSION, self::ANTIMATTER => 0,
            self::HYPERDRIVE => 1,
            self::WARP => 3,
            self::JUMPDRIVE => 10,
        };
    }

    public function isFtl(): bool
    {
        return $this->getMaxSystemRange() > 0;
    }

    /**
     * Welche Forschung Player haben muss um ein Schiff mit diesem Antrieb zu
     * bauen. NULL = keine Voraussetzung (HYDROGEN = Foundation-Standard).
     */
    public function getRequiredResearchSlug(): ?string
    {
        return match ($this) {
            self::HYDROGEN => null,
            self::ION => 'propulsion_ion',
            self::FUSION => 'propulsion_fusion',
            self::ANTIMATTER => 'propulsion_antimatter',
            self::HYPERDRIVE => 'ftl_hyperdrive',
            self::WARP => 'ftl_warp',
            self::JUMPDRIVE => 'ftl_jumpdrive',
        };
    }
}
