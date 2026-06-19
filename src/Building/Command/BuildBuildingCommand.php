<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;

/**
 * @implements CommandInterface<Building>
 */
class BuildBuildingCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public BuildingType $type,
    ) {
    }
}
