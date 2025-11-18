<?php

namespace App\Common\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractUuid
{
    protected UuidInterface $uuid;

    public function __construct(string|UuidInterface $uuid = null)
    {
        if ($uuid === null) {
            $this->uuid = Uuid::uuid4();
        } elseif (is_string($uuid)) {
            if (!Uuid::isValid($uuid)) {
                throw new InvalidArgumentException("Invalid UUID string: $uuid");
            }
            $this->uuid = Uuid::fromString($uuid);
        } else {
            $this->uuid = $uuid;
        }
    }

    public static function generate(): static
    {
        return new static(Uuid::uuid4());
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    public function equals(AbstractUuid $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    public function getValue(): UuidInterface
    {
        return $this->uuid;
    }
}
