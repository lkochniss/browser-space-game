<?php

declare(strict_types=1);

namespace App\Planet\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Planet>
 */
class ColonizePlanetCommand implements CommandInterface
{
    public function __construct(
        public ShipId $shipId,
        public PlanetId $targetPlanetId,
    ) {
    }
}
