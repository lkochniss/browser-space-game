<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use DomainException;

/**
 * T-104a: Player will Crew trainieren, aber Officer-Quarters-Cap erreicht.
 */
final class CrewCapReachedException extends DomainException
{
    public function __construct(public readonly int $current, public readonly int $cap)
    {
        parent::__construct(sprintf(
            'Crew-Cap erreicht (%d/%d) — baue Officer-Quarters für mehr Slots.',
            $current,
            $cap,
        ));
    }
}
