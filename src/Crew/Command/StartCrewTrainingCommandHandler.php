<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Crew\Model\Crew;
use App\Crew\Service\StartCrewTrainingCommandService;

class StartCrewTrainingCommandHandler implements CommandHandlerInterface
{
    public function __construct(private StartCrewTrainingCommandService $service)
    {
    }

    public function __invoke(StartCrewTrainingCommand $command): Crew
    {
        return $this->service->__invoke($command->playerId, $command->type);
    }
}
