<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Trade\Model\TradeRoute;
use App\Trade\Service\CreateTradeRouteCommandService;

class CreateFixedRouteCommandHandler implements CommandHandlerInterface
{
    public function __construct(private CreateTradeRouteCommandService $service)
    {
    }

    public function __invoke(CreateFixedRouteCommand $command): TradeRoute
    {
        return $this->service->createFixed(
            $command->playerId,
            $command->shipId,
            $command->sourcePlanetId,
            $command->targetPlanetId,
            $command->outboundResource,
            $command->outboundQty,
            $command->returnResource,
            $command->returnQty,
        );
    }
}
