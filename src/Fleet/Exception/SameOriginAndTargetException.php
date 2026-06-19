<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

final class SameOriginAndTargetException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf(
            'Fleet target equals origin (%s) — no movement to schedule',
            $planetId,
        ));
    }
}
