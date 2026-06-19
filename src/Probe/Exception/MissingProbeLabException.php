<?php

declare(strict_types=1);

namespace App\Probe\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

final class MissingProbeLabException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf(
            'Planet "%s" needs a finished PROBE_LAB building to construct probes',
            $planetId,
        ));
    }
}
