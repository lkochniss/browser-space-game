<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Resource\ValueObject\ResourceType;
use DomainException;

class InsufficientResourcesException extends DomainException
{
    public function __construct(
        public readonly ResourceType $resourceType,
        public readonly int $required,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Insufficient %s: need %d, have %d',
            $resourceType->value,
            $required,
            $available,
        ));
    }
}
