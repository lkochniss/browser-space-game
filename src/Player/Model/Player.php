<?php

namespace App\Player\Model;

use App\Planet\Model\Planet;
use App\Planet\Model\PlanetCollection;
use ValueObject\PlayerId;

class Player
{
   public function __construct(private PlayerId $id, private PlanetCollection $planets)
   {
   }

    public function getId(): PlayerId
    {
        return $this->id;
    }

    public function getPlanets(): PlanetCollection
    {
        return $this->planets;
    }

    public function setPlanets(PlanetCollection $planets): void
    {
        $this->planets = $planets;
    }

    public function claimPlanet(Planet $planet): void
    {
        $this->planets->add($planet);
    }
}
