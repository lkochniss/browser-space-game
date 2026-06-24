<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Trade\Model\TradeRoute;
use App\Trade\Service\CreateTradeRouteCommandService;

class CreateSingleTripCommandHandler implements CommandHandlerInterface
{
    public function __construct(private CreateTradeRouteCommandService $service)
    {
    }

    public function __invoke(CreateSingleTripCommand $command): TradeRoute
    {
        return $this->service->createSingleTrip(
            $command->playerId,
            $command->shipId,
            $command->sourcePlanetId,
            $command->targetPlanetId,
            $command->resource,
            $command->qty,
        );
    }
}
