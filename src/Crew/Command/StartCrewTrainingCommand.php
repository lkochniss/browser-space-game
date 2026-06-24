<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandInterface;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewType;
use App\Player\ValueObject\PlayerId;

/**
 * T-104a Start eines Crew-Trainings in der Akademie.
 *
 * @implements CommandInterface<Crew>
 */
readonly class StartCrewTrainingCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public CrewType $type = CrewType::CAPTAIN,
    ) {
    }
}
