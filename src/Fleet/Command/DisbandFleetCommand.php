<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandInterface;
use App\Fleet\ValueObject\FleetId;

/**
 * @implements CommandInterface<null>
 */
class DisbandFleetCommand implements CommandInterface
{
    public function __construct(
        public FleetId $fleetId,
    ) {
    }
}
