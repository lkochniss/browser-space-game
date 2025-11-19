<?php

namespace App\Building\Model;

use ArrayIterator;
use IteratorAggregate;

class BuildingCollection implements IteratorAggregate
{
    /** @var Building[] */
    private array $buildings = [];

    public function __construct(array $buildings = [])
    {
        $this->buildings = $buildings;
    }

    public function add(Building $planet): void
    {
        $this->buildings[] = $planet;
    }

    /**
     * @return Building[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->buildings);
    }

    /**
     * @return Building[]
     */
    public function toArray(): array
    {
        return $this->buildings;
    }
}
