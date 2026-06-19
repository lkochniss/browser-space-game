<?php

declare(strict_types=1);

namespace App\Planet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Planet\Model\Planet;
use App\Planet\Service\ColonizePlanetCommandService;

class ColonizePlanetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private ColonizePlanetCommandService $service)
    {
    }

    public function __invoke(ColonizePlanetCommand $command): Planet
    {
        return $this->service->__invoke($command->shipId, $command->targetPlanetId);
    }
}
