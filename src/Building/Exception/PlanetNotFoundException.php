<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

class PlanetNotFoundException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf('Planet not found: %s', (string) $planetId));
    }
}
