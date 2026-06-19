<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class ShipNotReadyException extends DomainException
{
    public function __construct(public readonly ShipId $shipId)
    {
        parent::__construct(sprintf(
            'Ship "%s" is still under construction',
            $shipId,
        ));
    }
}
