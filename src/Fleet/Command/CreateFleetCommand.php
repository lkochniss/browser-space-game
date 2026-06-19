<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandInterface;
use App\Fleet\Model\Fleet;
use App\Player\ValueObject\PlayerId;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Fleet>
 */
class CreateFleetCommand implements CommandInterface
{
    /**
     * @param list<ShipId> $shipIds Mindestens 1, alle docked am gleichen Planet,
     *                              alle gehören dem `playerId`, alle isReady,
     *                              keines bereits in einer anderen Fleet.
     */
    public function __construct(
        public PlayerId $playerId,
        public array $shipIds,
    ) {
    }
}
