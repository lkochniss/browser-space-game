<?php

declare(strict_types=1);

namespace App\POI\Exception;

use App\SolarSystem\ValueObject\SolarSystemId;
use DomainException;

final class SolarSystemNotFoundException extends DomainException
{
    public function __construct(public readonly SolarSystemId $systemId)
    {
        parent::__construct(sprintf('SolarSystem "%s" not found', $systemId));
    }
}
