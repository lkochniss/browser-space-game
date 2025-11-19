<?php

namespace App\Resource\Model;

use ArrayIterator;
use IteratorAggregate;

class ResourceCollection implements IteratorAggregate
{
    /** @var Resource[] */
    private array $resources = [];

    public function __construct(array $resources = [])
    {
        $this->resources = $resources;
    }

    public function add(Resource $planet): void
    {
        $this->resources[] = $planet;
    }

    /**
     * @return Resource[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->resources);
    }

    /**
     * @return Resource[]
     */
    public function toArray(): array
    {
        return $this->resources;
    }
}
