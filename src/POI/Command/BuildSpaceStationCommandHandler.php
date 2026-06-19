<?php

declare(strict_types=1);

namespace App\POI\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\POI\Model\SpaceStation;
use App\POI\Service\BuildSpaceStationCommandService;

class BuildSpaceStationCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BuildSpaceStationCommandService $service)
    {
    }

    public function __invoke(BuildSpaceStationCommand $command): SpaceStation
    {
        return $this->service->__invoke($command->playerId, $command->solarSystemId);
    }
}
