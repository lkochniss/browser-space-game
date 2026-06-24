<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\ValueObject\ShipId;
use App\Trade\Model\TradeRoute;

/**
 * T-110 Single-Trip: One-way source → target, Ship bleibt am Target.
 *
 * @implements CommandInterface<TradeRoute>
 */
class CreateSingleTripCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public ShipId $shipId,
        public PlanetId $sourcePlanetId,
        public PlanetId $targetPlanetId,
        public ResourceType $resource,
        public int $qty,
    ) {
    }
}
