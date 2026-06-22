<?php

declare(strict_types=1);

namespace App\Player\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Player\Model\Player;
use App\Player\Service\SetPlayerBackgroundCommandService;

class SetPlayerBackgroundCommandHandler implements CommandHandlerInterface
{
    public function __construct(private SetPlayerBackgroundCommandService $service)
    {
    }

    public function __invoke(SetPlayerBackgroundCommand $command): Player
    {
        return $this->service->__invoke($command->playerId, $command->background);
    }
}
