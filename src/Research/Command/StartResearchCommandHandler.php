<?php

declare(strict_types=1);

namespace App\Research\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Research\Model\ActiveResearch;
use App\Research\Service\StartResearchCommandService;

class StartResearchCommandHandler implements CommandHandlerInterface
{
    public function __construct(private StartResearchCommandService $service)
    {
    }

    public function __invoke(StartResearchCommand $command): ActiveResearch
    {
        return $this->service->__invoke(
            $command->playerId,
            $command->nodeSlug,
            $command->primaryLabPlanetId,
            $command->boosterLabPlanetIds,
        );
    }
}
