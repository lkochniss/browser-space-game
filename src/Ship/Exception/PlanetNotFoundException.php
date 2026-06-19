<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

final class PlanetNotFoundException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf('Planet "%s" not found', $planetId));
    }
}
