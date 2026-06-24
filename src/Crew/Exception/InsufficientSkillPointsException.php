<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Crew\ValueObject\CrewId;

final class InsufficientSkillPointsException extends \DomainException
{
    public function __construct(CrewId $crewId)
    {
        parent::__construct(sprintf('Crew %s has no available Skill-Points.', $crewId));
    }
}
