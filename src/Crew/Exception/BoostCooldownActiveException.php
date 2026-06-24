<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Crew\Model\Crew;
use DomainException;

/**
 * T-104a Boost-Cooldown 24h pro Captain.
 */
final class BoostCooldownActiveException extends DomainException
{
    public function __construct(Crew $crew, int $secondsLeft)
    {
        parent::__construct(sprintf(
            'Crew %s Boost-Cooldown noch %d Sekunden aktiv.',
            $crew->getId(),
            $secondsLeft,
        ));
    }
}
