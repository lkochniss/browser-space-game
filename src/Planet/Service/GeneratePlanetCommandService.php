<?php

declare(strict_types=1);

namespace App\Planet\Service;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;

class GeneratePlanetCommandService
{
    public function __invoke(PlanetId $planetId): Planet
    {
        return Planet::generatePlanet($planetId);
    }
}
