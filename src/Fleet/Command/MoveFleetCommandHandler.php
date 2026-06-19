<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Fleet\Model\Fleet;
use App\Fleet\Service\MoveFleetCommandService;

class MoveFleetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private MoveFleetCommandService $service)
    {
    }

    public function __invoke(MoveFleetCommand $command): Fleet
    {
        return $this->service->__invoke($command->fleetId, $command->targetPlanetId);
    }
}
