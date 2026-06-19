<?php

declare(strict_types=1);

namespace App\Planet\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class ColonyShipNotDockedException extends DomainException
{
    public function __construct(public readonly ShipId $shipId)
    {
        parent::__construct(sprintf(
            'Colony-Ship "%s" has no home planet — cannot determine pop-source for colonization',
            $shipId,
        ));
    }
}
