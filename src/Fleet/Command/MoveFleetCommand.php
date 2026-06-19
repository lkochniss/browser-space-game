<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandInterface;
use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetId;
use App\Planet\ValueObject\PlanetId;

/**
 * @implements CommandInterface<Fleet>
 */
class MoveFleetCommand implements CommandInterface
{
    public function __construct(
        public FleetId $fleetId,
        public PlanetId $targetPlanetId,
    ) {
    }
}
