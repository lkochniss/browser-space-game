<?php

namespace App\Planet\Model;

use ArrayIterator;
use IteratorAggregate;

class PlanetCollection implements IteratorAggregate
{
    /** @var Planet[] */
    private array $planets = [];

    public function __construct(array $planets = [])
    {
        $this->planets = $planets;
    }

    public function add(Planet $planet): void
    {
        $this->planets[] = $planet;
    }

    /**
     * @return Planet[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->planets);
    }

    /**
     * @return Planet[]
     */
    public function toArray(): array
    {
        return $this->planets;
    }
}
