<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Ship\Model\Ship;
use App\Ship\Service\LoadCargoCommandService;

class LoadCargoCommandHandler implements CommandHandlerInterface
{
    public function __construct(private LoadCargoCommandService $service)
    {
    }

    public function __invoke(LoadCargoCommand $command): Ship
    {
        return $this->service->__invoke($command->shipId, $command->resources, $command->popCount);
    }
}
