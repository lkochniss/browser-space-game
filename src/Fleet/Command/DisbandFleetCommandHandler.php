<?php

declare(strict_types=1);

namespace App\Fleet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Fleet\Service\DisbandFleetCommandService;

class DisbandFleetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private DisbandFleetCommandService $service)
    {
    }

    public function __invoke(DisbandFleetCommand $command): null
    {
        $this->service->__invoke($command->fleetId);

        return null;
    }
}
