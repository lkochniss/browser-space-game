<?php

namespace App\Player\Model;

use App\Planet\Model\Planet;
use ValueObject\PlayerId;

class Player
{
    /**
     * @param iterable<Planet> $planets
     */
   public function __construct(private PlayerId $id, private iterable $planets)
   {
   }

    public function getId(): PlayerId
    {
        return $this->id;
    }

    /**
     * @return iterable<Planet>
     */
    public function getPlanets(): iterable
    {
        return $this->planets;
    }

    /**
     * @param iterable<Planet> $planets
     * @return void
     */
    public function setPlanets(iterable $planets): void
    {
        $this->planets = $planets;
    }
}
