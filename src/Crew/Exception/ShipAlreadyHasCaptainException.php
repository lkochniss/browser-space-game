<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

/**
 * T-104a: Schiff hat bereits einen Captain assigned.
 */
final class ShipAlreadyHasCaptainException extends DomainException
{
    public function __construct(public readonly ShipId $shipId)
    {
        parent::__construct(sprintf(
            'Ship %s hat bereits einen Captain assigned. Vorher unassignen.',
            $shipId,
        ));
    }
}
