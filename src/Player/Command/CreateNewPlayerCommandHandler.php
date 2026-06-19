<?php

namespace App\Player\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Player\Model\Player;
use App\Player\Service\CreateNewPlayerService;
use App\Player\ValueObject\PlayerId;

class CreateNewPlayerCommandHandler implements CommandHandlerInterface
{
    public function __construct(private CreateNewPlayerService $service)
    {
    }

    public function __invoke(CreateNewPlayerCommand $command): Player
    {
        return $this->service->__invoke(PlayerId::generate());
    }
}
