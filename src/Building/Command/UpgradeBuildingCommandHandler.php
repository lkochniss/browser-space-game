<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\Service\UpgradeBuildingCommandService;
use App\Common\Interface\CommandHandlerInterface;

class UpgradeBuildingCommandHandler implements CommandHandlerInterface
{
    public function __construct(private UpgradeBuildingCommandService $service)
    {
    }

    public function __invoke(UpgradeBuildingCommand $command): Building
    {
        return $this->service->__invoke($command->planetId, $command->buildingId);
    }
}
