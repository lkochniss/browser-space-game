<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;

/**
 * T-068: Stellt ein beschädigtes Defense-Building voll wieder her.
 * 30% Initial-Build-Cost auf current-Level + 24h Cooldown.
 *
 * @implements CommandInterface<Building>
 */
class RepairBuildingCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public BuildingId $buildingId,
    ) {
    }
}
