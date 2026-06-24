<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Crew\Model\Crew;
use DomainException;

/**
 * T-104a: Captain-Assignment / Boost / etc. fordert IDLE-Status.
 */
final class CrewNotIdleException extends DomainException
{
    public function __construct(Crew $crew)
    {
        parent::__construct(sprintf(
            'Crew %s ist nicht IDLE (aktuell: %s).',
            $crew->getId(),
            $crew->getStatus()->value,
        ));
    }
}
