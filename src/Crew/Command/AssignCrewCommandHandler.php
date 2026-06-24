<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Crew\Model\Crew;
use App\Crew\Service\AssignCrewCommandService;

class AssignCrewCommandHandler implements CommandHandlerInterface
{
    public function __construct(private AssignCrewCommandService $service)
    {
    }

    public function __invoke(AssignCrewCommand $command): Crew
    {
        return $this->service->__invoke($command->crewId, $command->shipId);
    }
}
