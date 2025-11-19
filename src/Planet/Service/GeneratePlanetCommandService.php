<?php

namespace App\Planet\Service;

use App\Planet\Model\Planet;
use ValueObject\PlanetId;

class GeneratePlanetCommandService
{
    public function __invoke(PlanetId $planetId): Planet
    {
        return Planet::generatePlanet();
    }
}
