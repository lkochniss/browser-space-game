<?php

declare(strict_types=1);

namespace App\Planet\Exception;

use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DomainException;

final class NotAColonyShipException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly ShipType $actualType,
    ) {
        parent::__construct(sprintf(
            'Ship "%s" is %s, expected COLONY_SHIP for colonization',
            $shipId,
            $actualType->value,
        ));
    }
}
