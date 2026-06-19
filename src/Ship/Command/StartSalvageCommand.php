<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Ship>
 *
 * T-016 Echtzeit-Salvage starten. Schiff salvaget mit fester Rate (T-127
 * skaliert Rate später) bis Field-Empty oder Cargo-Voll.
 */
class StartSalvageCommand implements CommandInterface
{
    public function __construct(
        public ShipId $shipId,
        public PoiId $poiId,
        public ResourceType $resourceType,
    ) {
    }
}
