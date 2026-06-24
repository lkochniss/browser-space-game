<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Crew\Model\Crew;
use App\Crew\Service\BoostCrewCommandService;

class BoostCrewCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BoostCrewCommandService $service)
    {
    }

    public function __invoke(BoostCrewCommand $command): Crew
    {
        return $this->service->__invoke($command->crewId);
    }
}
