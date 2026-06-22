<?php

declare(strict_types=1);

namespace App\Resource\Exception;

use App\Resource\ValueObject\ResourceType;
use DomainException;

/**
 * T-180: Versuch eines Volume-Lookups für einen Resource-Type, für den noch
 * kein Multiplier in `ResourceVolumeConfig` registriert wurde.
 *
 * Fail-fast — verhindert dass neue ResourceTypes still mit Default 0 (oder
 * 1.0) laufen und Storage-Berechnungen verfälschen.
 */
class UnknownResourceVolumeException extends DomainException
{
    public function __construct(ResourceType $type)
    {
        parent::__construct(sprintf(
            'Kein Volume-Multiplier registriert für ResourceType "%s". Ergänze ResourceVolumeConfig::MULTIPLIERS.',
            $type->value,
        ));
    }
}
