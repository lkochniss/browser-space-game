<?php

namespace App\Planet\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Planet\Model\Planet;
use App\Planet\Service\GeneratePlanetCommandService;
use ValueObject\PlanetId;

class GeneratePlanetCommandHandler implements CommandHandlerInterface
{
    public function __construct(private GeneratePlanetCommandService $service)
    {
    }

    public function __invoke(GeneratePlanetCommand $command): Planet
    {
        return $this->service->__invoke(PlanetId::generate());
    }
}
