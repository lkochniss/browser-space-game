<?php

namespace App\Resource\Model;

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

    public static function generateDepositWithAmount(ResourceType $type, int $amount): self
    {
        return new self(ResourceDepositId::generate(), $type, $amount);
    }
}
