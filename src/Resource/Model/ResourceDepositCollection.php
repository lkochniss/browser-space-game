<?php

namespace App\Resource\Model;

use ArrayIterator;
use IteratorAggregate;

class ResourceDepositCollection implements IteratorAggregate
{
    /** @var ResourceDeposit[] */
    private array $resourceDeposits = [];

    public function __construct(array $resourceDeposits = [])
    {
        $this->resourceDeposits = $resourceDeposits;
    }

    public function add(ResourceDeposit $planet): void
    {
        $this->resourceDeposits[] = $planet;
    }

    /**
     * @return ResourceDeposit[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->resourceDeposits);
    }

    /**
     * @return ResourceDeposit[]
     */
    public function toArray(): array
    {
        return $this->resourceDeposits;
    }
}
