<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

final class MissingShipyardException extends DomainException
{
    public function __construct(public readonly PlanetId $planetId)
    {
        parent::__construct(sprintf(
            'Planet "%s" needs a finished SHIPYARD building to construct ships',
            $planetId,
        ));
    }
}
