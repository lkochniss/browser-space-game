<?php

declare(strict_types=1);

namespace App\Trade\ValueObject;

/**
 * T-110 Trade-Route-Lifecycle.
 *
 * - `ACTIVE`: Fixed-Route, läuft auto (Outbound→Target→Return→Source-Loop)
 * - `SINGLE_TRIP`: One-way Lieferung, nach Outbound-Delivery → CANCELLED
 * - `PAUSED`: Route hält an, Ship parkt am aktuellen Planeten (Inspect-Mode)
 * - `CANCELLED`: Route ist beendet, Ship frei, Entity bleibt für History
 */
enum TradeRouteStatus: string
{
    case ACTIVE = 'active';
    case SINGLE_TRIP = 'single_trip';
    case PAUSED = 'paused';
    case CANCELLED = 'cancelled';

    public function isRunning(): bool
    {
        return $this === self::ACTIVE || $this === self::SINGLE_TRIP;
    }
}
