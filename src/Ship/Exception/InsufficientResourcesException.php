<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Resource\ValueObject\ResourceType;
use DomainException;

final class InsufficientResourcesException extends DomainException
{
    public function __construct(
        public readonly ResourceType $resourceType,
        public readonly int $required,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Insufficient %s for ship build: need %d, have %d',
            $resourceType->value,
            $required,
            $available,
        ));
    }
}
