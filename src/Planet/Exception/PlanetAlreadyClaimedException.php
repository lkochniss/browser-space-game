<?php

declare(strict_types=1);

namespace App\Planet\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

final class PlanetAlreadyClaimedException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf(
            'Planet "%s" is already owned by a player and cannot be colonized',
            $planetId,
        ));
    }
}
