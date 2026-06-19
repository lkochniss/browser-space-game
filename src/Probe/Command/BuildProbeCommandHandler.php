<?php

declare(strict_types=1);

namespace App\Probe\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Probe\Model\Probe;
use App\Probe\Service\BuildProbeCommandService;

class BuildProbeCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BuildProbeCommandService $service)
    {
    }

    public function __invoke(BuildProbeCommand $command): Probe
    {
        return $this->service->__invoke($command->planetId, $command->type);
    }
}
