<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Ship\Model\Ship;
use App\Ship\Service\UnloadCargoCommandService;

class UnloadCargoCommandHandler implements CommandHandlerInterface
{
    public function __construct(private UnloadCargoCommandService $service)
    {
    }

    public function __invoke(UnloadCargoCommand $command): Ship
    {
        return $this->service->__invoke($command->shipId, $command->resources, $command->popCount);
    }
}
