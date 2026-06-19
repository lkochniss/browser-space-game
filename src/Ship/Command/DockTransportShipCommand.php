<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Ship>
 *
 * T-015 Foundation-Stub für Movement: setzt Schiff "magisch" am Ziel-Planet.
 * T-017 Flotte-Movement ersetzt das durch echtes Wallclock-Travel.
 */
class DockTransportShipCommand implements CommandInterface
{
    public function __construct(
        public ShipId $shipId,
        public PlanetId $targetPlanetId,
    ) {
    }
}
