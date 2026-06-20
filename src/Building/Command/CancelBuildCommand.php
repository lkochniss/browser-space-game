<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;

/**
 * T-094b: Bricht laufenden Build/Upgrade ab.
 *
 * @implements CommandInterface<?Building>
 */
readonly class CancelBuildCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public BuildingId $buildingId,
    ) {
    }
}
