<?php

declare(strict_types=1);

namespace App\Fleet\Service;

/**
 * T-017 Foundation-Stub für Reisezeiten. Pauschal pro Hop-Type.
 *
 * - Inter-Planet (gleiches System): Default 30min
 * - Inter-System (anderes System): Default 4h
 *
 * `effectiveDuration = base / fleetMinSpeed` — langsamstes Schiff der Fleet
 * bestimmt, also kann sich Travel-Time z.B. bei TRANSPORT_LARGE (Speed 0.6)
 * auf 30min/0.6 = 50min strecken.
 *
 * Spätere Erweiterung: T-026 Antriebs-Tech, T-160 Galaxy-Map (Distance pro
 * System-Hop).
 */
class FleetMovementConfig
{
    public const INTRA_SYSTEM_BASE_SECONDS = 1800;   // 30min
    public const INTER_SYSTEM_BASE_SECONDS = 14400;  // 4h

    public function getBaseDurationSeconds(bool $sameSystem): int
    {
        return $sameSystem
            ? self::INTRA_SYSTEM_BASE_SECONDS
            : self::INTER_SYSTEM_BASE_SECONDS;
    }

    /**
     * Effective Travel-Duration in Sekunden.
     * fleetMinSpeed = min(getSpeed) aller Schiffe in der Fleet.
     */
    public function computeDurationSeconds(bool $sameSystem, float $fleetMinSpeed): int
    {
        if ($fleetMinSpeed <= 0.0) {
            $fleetMinSpeed = 1.0;
        }
        $base = $this->getBaseDurationSeconds($sameSystem);

        return (int) max(60, round($base / $fleetMinSpeed));
    }
}
