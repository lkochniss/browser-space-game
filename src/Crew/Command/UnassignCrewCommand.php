<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandInterface;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewId;

/**
 * @implements CommandInterface<Crew>
 */
readonly class UnassignCrewCommand implements CommandInterface
{
    public function __construct(public CrewId $crewId)
    {
    }
}
