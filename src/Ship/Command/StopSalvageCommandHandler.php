<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Ship\Model\Ship;
use App\Ship\Service\StopSalvageCommandService;

class StopSalvageCommandHandler implements CommandHandlerInterface
{
    public function __construct(private StopSalvageCommandService $service)
    {
    }

    public function __invoke(StopSalvageCommand $command): Ship
    {
        return $this->service->__invoke($command->shipId);
    }
}
