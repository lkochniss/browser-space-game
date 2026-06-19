<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Ship\Model\Ship;
use App\Ship\Service\StartSalvageCommandService;

class StartSalvageCommandHandler implements CommandHandlerInterface
{
    public function __construct(private StartSalvageCommandService $service)
    {
    }

    public function __invoke(StartSalvageCommand $command): Ship
    {
        return $this->service->__invoke($command->shipId, $command->poiId, $command->resourceType);
    }
}
