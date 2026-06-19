<?php

namespace App\Player\Service;

use App\Common\Interface\CommandBusInterface;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Command\GeneratePlanetCommand;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;

class CreateNewPlayerService
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    public function __invoke(PlayerId $playerId): Player
    {
        $planet = $this->commandBus->dispatch(
            new GeneratePlanetCommand()
        );

        return $this->commandBus->dispatch(
            new ClaimStartPlanetCommand($playerId, $planet->getId())
        );
    }
}
