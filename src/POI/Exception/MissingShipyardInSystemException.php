<?php

declare(strict_types=1);

namespace App\POI\Exception;

use App\SolarSystem\ValueObject\SolarSystemId;
use DomainException;

final class MissingShipyardInSystemException extends DomainException
{
    public function __construct(
        public readonly SolarSystemId $systemId,
        public readonly int $requiredLevel,
    ) {
        parent::__construct(sprintf(
            'Player needs a Shipyard >= Level %d in system "%s" to build a SpaceStation',
            $requiredLevel,
            $systemId,
        ));
    }
}
