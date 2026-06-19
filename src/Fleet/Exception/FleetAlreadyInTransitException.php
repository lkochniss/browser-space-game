<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use App\Fleet\ValueObject\FleetId;
use DomainException;

final class FleetAlreadyInTransitException extends DomainException
{
    public function __construct(public readonly FleetId $fleetId)
    {
        parent::__construct(sprintf(
            'Fleet "%s" is already IN_TRANSIT — wait for arrival',
            $fleetId,
        ));
    }
}
