<?php

namespace App\Planet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Planet\Service\ClaimStartPlanetCommandService;
use App\Player\Model\Player;

class ClaimStartPlanetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private ClaimStartPlanetCommandService $service)
    {
    }

    public function __invoke(ClaimStartPlanetCommand $command): Player
    {
        return $this->service->__invoke($command->playerId, $command->planetId);
    }
}
