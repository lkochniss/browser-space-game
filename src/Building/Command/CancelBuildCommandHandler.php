<?php

declare(strict_types=1);

namespace App\Building\Command;

use App\Building\Model\Building;
use App\Building\Service\CancelBuildCommandService;
use App\Common\Interface\CommandHandlerInterface;

class CancelBuildCommandHandler implements CommandHandlerInterface
{
    public function __construct(private CancelBuildCommandService $service)
    {
    }

    public function __invoke(CancelBuildCommand $command): ?Building
    {
        return $this->service->__invoke($command->planetId, $command->buildingId);
    }
}
