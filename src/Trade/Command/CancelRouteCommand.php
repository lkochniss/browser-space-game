<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandInterface;
use App\Trade\Model\TradeRoute;
use App\Trade\ValueObject\TradeRouteId;

/** @implements CommandInterface<TradeRoute> */
class CancelRouteCommand implements CommandInterface
{
    public function __construct(public TradeRouteId $routeId)
    {
    }
}
