<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandInterface;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewId;

/**
 * T-104a Crew bekommt XP-Boost via Resource-Investment.
 * Cost: 500 IRON_BAR + 100 CHIP → +500 XP. Cooldown 24h.
 *
 * @implements CommandInterface<Crew>
 */
readonly class BoostCrewCommand implements CommandInterface
{
    public function __construct(public CrewId $crewId)
    {
    }
}
