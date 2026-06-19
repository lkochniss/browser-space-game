<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingId;
use App\Planet\ValueObject\PlanetId;
use DomainException;

class BuildingNotFoundException extends DomainException
{
    public function __construct(
        public readonly PlanetId $planetId,
        public readonly BuildingId $buildingId,
    ) {
        parent::__construct(sprintf(
            'Building %s not found on planet %s',
            (string) $buildingId,
            (string) $planetId,
        ));
    }
}
