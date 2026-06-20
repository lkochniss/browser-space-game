<?php

declare(strict_types=1);

namespace App\Building\Exception;

use DomainException;

/**
 * T-171: Planet-Slot-Cap erreicht — Building-Size + bestehende Slot-Belegung
 * würde den Cap überschreiten. Spieler muss bestehende Buildings upgraden statt
 * neue zu bauen, oder einen größeren Planeten kolonisieren.
 */
class PlanetSlotsFullException extends DomainException
{
    public function __construct(int $used, int $cap, int $needed)
    {
        parent::__construct(sprintf(
            'Planet-Slots voll: %d/%d belegt, neuer Bau braucht %d Slot(s). Upgrade existierende Buildings statt neuer Instanzen, oder besiedele einen größeren Planeten.',
            $used,
            $cap,
            $needed,
        ));
    }
}
