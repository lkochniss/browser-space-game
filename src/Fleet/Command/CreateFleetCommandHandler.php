<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Fleet\Model\Fleet;
use App\Fleet\Service\CreateFleetCommandService;

class CreateFleetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private CreateFleetCommandService $service)
    {
    }

    public function __invoke(CreateFleetCommand $command): Fleet
    {
        return $this->service->__invoke($command->playerId, $command->shipIds);
    }
}
