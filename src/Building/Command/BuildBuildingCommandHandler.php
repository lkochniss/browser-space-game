<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\Service\BuildBuildingCommandService;
use App\Common\Interface\CommandHandlerInterface;

class BuildBuildingCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BuildBuildingCommandService $service)
    {
    }

    public function __invoke(BuildBuildingCommand $command): Building
    {
        return $this->service->__invoke($command->planetId, $command->type);
    }
}
