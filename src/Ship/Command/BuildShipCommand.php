<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipType;

/**
 * @implements CommandInterface<Ship>
 */
class BuildShipCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public ShipType $type = ShipType::GENERIC,
    ) {
    }
}
