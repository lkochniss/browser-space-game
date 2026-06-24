<?php

declare(strict_types=1);

namespace App\Battle\Command;

use App\Battle\Model\Battle;
use App\Common\Interface\CommandInterface;
use App\Fleet\ValueObject\FleetId;
use App\Planet\ValueObject\PlanetId;

/**
 * T-103: Start Fleet-vs-Fleet ODER Fleet-vs-Planet Battle.
 * Genau eines der beiden Defender-Felder muss gesetzt sein.
 *
 * @implements CommandInterface<Battle>
 */
class StartBattleCommand implements CommandInterface
{
    public function __construct(
        public FleetId $attackerFleetId,
        public ?FleetId $defenderFleetId = null,
        public ?PlanetId $defenderPlanetId = null,
    ) {
    }
}
