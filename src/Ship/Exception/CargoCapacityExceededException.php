<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class CargoCapacityExceededException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly int $requested,
        public readonly int $free,
    ) {
        parent::__construct(sprintf(
            'Cargo capacity exceeded for ship "%s": tried to load %d units, only %d free',
            $shipId,
            $requested,
            $free,
        ));
    }
}
