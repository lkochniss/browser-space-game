<?php

declare(strict_types=1);

namespace App\POI\Command;

use App\Common\Interface\CommandInterface;
use App\POI\Model\SpaceStation;
use App\Player\ValueObject\PlayerId;
use App\SolarSystem\ValueObject\SolarSystemId;

/**
 * @implements CommandInterface<SpaceStation>
 */
class BuildSpaceStationCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public SolarSystemId $solarSystemId,
    ) {
    }
}
