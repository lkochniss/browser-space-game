<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

/**
 * T-178 Volume-Cap überschritten beim Load/Unload.
 *
 * Ersetzt `CargoCapacityExceededException` für Ship-Cargo-Pfad
 * (Station-Pfad nutzt weiter Units-basiertes Cap → eigene Exception bleibt).
 */
final class ShipCargoOverflowException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly int $requestedVolume,
        public readonly int $freeVolume,
    ) {
        parent::__construct(sprintf(
            'Ship %s cargo volume exceeded: requested %d m³, only %d m³ free',
            (string) $shipId,
            $requestedVolume,
            $freeVolume,
        ));
    }
}
