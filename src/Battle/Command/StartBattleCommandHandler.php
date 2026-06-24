<?php

declare(strict_types=1);

namespace App\Battle\Command;

use App\Battle\Model\Battle;
use App\Battle\Service\StartBattleCommandService;
use App\Common\Interface\CommandHandlerInterface;

class StartBattleCommandHandler implements CommandHandlerInterface
{
    public function __construct(private StartBattleCommandService $service)
    {
    }

    public function __invoke(StartBattleCommand $command): Battle
    {
        return $this->service->__invoke(
            $command->attackerFleetId,
            $command->defenderFleetId,
            $command->defenderPlanetId,
        );
    }
}
