<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use App\Fleet\ValueObject\FleetId;
use DomainException;

final class FleetNotFoundException extends DomainException
{
    public function __construct(public readonly FleetId $fleetId)
    {
        parent::__construct(sprintf('Fleet "%s" not found', $fleetId));
    }
}
