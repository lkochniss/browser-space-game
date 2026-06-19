<?php

declare(strict_types=1);

namespace App\Probe\ValueObject;

/**
 * T-013 Sondentypen.
 *
 * - SYSTEM: One-shot, scannt komplettes System (Meta: alle Planeten + POIs sichtbar).
 * - ORBITAL: bleibt im Orbit, kontinuierliche Telemetrie eines Planeten/POIs.
 * - DEEP_SCAN: Tiefenscan für versteckte POIs + seltene Resources (Endgame-Tier).
 *
 * Discovery-Effekte werden in T-018 (Teleskop) / T-087 (Fog-of-War) materialisiert.
 * T-013 baut nur die Probe-Entity + Capabilities-Stub.
 */
enum ProbeType: string
{
    case SYSTEM = 'system';
    case ORBITAL = 'orbital';
    case DEEP_SCAN = 'deep_scan';

    /**
     * Reichweite in System-Distanz-Einheiten. Foundation-Stub:
     * SYSTEM = lokal (1), ORBITAL = lokal (1), DEEP_SCAN = mittlere Reichweite (3).
     * Konkrete Galaxy-Distance-Mechanik kommt mit T-160 / T-085 / T-018.
     */
    public function getRange(): int
    {
        return match ($this) {
            self::SYSTEM, self::ORBITAL => 1,
            self::DEEP_SCAN => 3,
        };
    }

    /**
     * One-shot Sonden werden nach erstem Scan verbraucht. Orbital bleibt persistent.
     */
    public function isOneShot(): bool
    {
        return match ($this) {
            self::SYSTEM, self::DEEP_SCAN => true,
            self::ORBITAL => false,
        };
    }
}
