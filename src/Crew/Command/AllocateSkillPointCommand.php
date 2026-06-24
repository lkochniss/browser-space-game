<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandInterface;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CaptainSkillTree;
use App\Crew\ValueObject\CrewId;

/**
 * T-104b: Allokiert 1 Skill-Punkt in den gewählten Tree (Tier-Lock implizit
 * sequentiell). Permanent — kein Re-Spec.
 *
 * @implements CommandInterface<Crew>
 */
class AllocateSkillPointCommand implements CommandInterface
{
    public function __construct(
        public CrewId $crewId,
        public CaptainSkillTree $tree,
    ) {
    }
}
