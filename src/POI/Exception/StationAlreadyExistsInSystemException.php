<?php

declare(strict_types=1);

namespace App\POI\Exception;

use App\SolarSystem\ValueObject\SolarSystemId;
use DomainException;

final class StationAlreadyExistsInSystemException extends DomainException
{
    public function __construct(public readonly SolarSystemId $systemId)
    {
        parent::__construct(sprintf(
            'Solar system "%s" already has a SpaceStation — max 1 per system',
            $systemId,
        ));
    }
}
