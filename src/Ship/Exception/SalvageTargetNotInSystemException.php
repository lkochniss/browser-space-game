<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class SalvageTargetNotInSystemException extends DomainException
{
    public function __construct(public readonly ShipId $shipId)
    {
        parent::__construct(sprintf(
            'Ship "%s" is not in the same solar system as the salvage target',
            $shipId,
        ));
    }
}
