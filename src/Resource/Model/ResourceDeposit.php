<?php

namespace App\Resource\Model;

use App\Resource\ValueObject\BuildingId;
use App\Resource\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceDepositId;
use App\Resource\ValueObject\ResourceType;

class ResourceDeposit
{
    public function __construct(private ResourceDepositId $id, private ResourceType $resourceType, private int $amount)
    {
    }

    public function getId(): ResourceDepositId
    {
        return $this->id;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}
