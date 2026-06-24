<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\PropulsionType;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipType;

/**
 * @implements CommandInterface<Ship>
 *
 * T-102: Optional `shipClass` markiert Combat-Schiff. Bei NOT NULL nutzt der
 * Build-Service `ShipBlueprintRegistry` für Cost/Duration/Pop/Stats statt
 * `ShipCostConfig`, und validiert zusätzlich Shipyard-Level + Mark-Research +
 * Captain-Availability.
 */
class BuildShipCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public ShipType $type = ShipType::GENERIC,
        public PropulsionType $propulsion = PropulsionType::HYDROGEN,
        public ?ShipClass $shipClass = null,
    ) {
    }
}
