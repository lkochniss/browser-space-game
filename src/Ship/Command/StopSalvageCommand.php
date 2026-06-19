<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Ship>
 *
 * T-016 Salvage manuell stoppen. Idempotent — wenn nicht aktiv, no-op.
 */
class StopSalvageCommand implements CommandInterface
{
    public function __construct(public ShipId $shipId)
    {
    }
}
