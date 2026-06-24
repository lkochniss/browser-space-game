<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\Service\RepairBuildingCommandService;
use App\Common\Interface\CommandHandlerInterface;

class RepairBuildingCommandHandler implements CommandHandlerInterface
{
    public function __construct(private RepairBuildingCommandService $service)
    {
    }

    public function __invoke(RepairBuildingCommand $command): Building
    {
        return $this->service->__invoke($command->planetId, $command->buildingId);
    }
}
