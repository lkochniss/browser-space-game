<?php

namespace App\Resource\Model;

use App\Resource\ValueObject\ResourceId;
use App\Resource\ValueObject\ResourceType;

class Resource
{
    public function __construct(private ResourceId $id, private ResourceType $type, private int $amount)
    {
    }

    public function getId(): ResourceId
    {
        return $this->id;
    }

    public function getType(): ResourceType
    {
        return $this->type;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public static function generateEmptyResource(ResourceType $type): self
    {
        return new self(ResourceId::generate(), $type, 0);
    }
}
