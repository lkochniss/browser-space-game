<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Ship\Model\Ship;
use App\Ship\Service\BuildShipCommandService;

class BuildShipCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BuildShipCommandService $service)
    {
    }

    public function __invoke(BuildShipCommand $command): Ship
    {
        return $this->service->__invoke(
            $command->planetId,
            $command->type,
            $command->propulsion,
            $command->shipClass,
        );
    }
}
