<?php

declare(strict_types=1);

namespace App\Resource\Exception;

use App\Resource\ValueObject\ResourceType;
use DomainException;

/**
 * Aggregat-Resource-Check über alle Player-Planeten (z.B. Research-Cost,
 * Crew-Boost-Cost).
 */
final class InsufficientPlayerResourcesException extends DomainException
{
    public function __construct(
        public readonly ResourceType $resource,
        public readonly int $required,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Player hat %d %s, benötigt aber %d (über alle Planeten aggregiert).',
            $available,
            $resource->value,
            $required,
        ));
    }
}
