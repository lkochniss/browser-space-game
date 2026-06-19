<?php

namespace App\Planet\Command;

use App\Common\Interface\CommandInterface;
use App\Player\Model\Player;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;

/**
 * @implements CommandInterface<Player>
 */
class ClaimStartPlanetCommand implements CommandInterface
{
    public function __construct(public PlayerId $playerId, public PlanetId $planetId)
    {
    }
}
