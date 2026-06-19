<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DomainException;

final class NotATransportShipException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly ShipType $actualType,
    ) {
        parent::__construct(sprintf(
            'Ship "%s" is %s, expected a TRANSPORT_* class for cargo operations',
            $shipId,
            $actualType->value,
        ));
    }
}
