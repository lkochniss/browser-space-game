<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Crew\ValueObject\CrewId;
use DomainException;

final class CrewNotFoundException extends DomainException
{
    public function __construct(public readonly CrewId $crewId)
    {
        parent::__construct(sprintf('Crew "%s" nicht gefunden.', $crewId));
    }
}
