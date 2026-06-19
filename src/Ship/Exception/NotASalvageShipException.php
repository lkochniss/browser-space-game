<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DomainException;

final class NotASalvageShipException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly ShipType $actualType,
    ) {
        parent::__construct(sprintf(
            'Ship "%s" is %s, expected SALVAGE for salvage actions',
            $shipId,
            $actualType->value,
        ));
    }
}
