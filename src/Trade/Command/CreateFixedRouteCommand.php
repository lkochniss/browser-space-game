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
 * T-110 Fixed-Route: bidirektional optional, Auto-Loop bis Cancel.
 *
 * @implements CommandInterface<TradeRoute>
 */
class CreateFixedRouteCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public ShipId $shipId,
        public PlanetId $sourcePlanetId,
        public PlanetId $targetPlanetId,
        public ResourceType $outboundResource,
        public int $outboundQty,
        public ?ResourceType $returnResource = null,
        public ?int $returnQty = null,
    ) {
    }
}
