<?php

declare(strict_types=1);

namespace App\Trade\Exception;

use App\Trade\ValueObject\TradeRouteId;

final class TradeRouteNotFoundException extends \DomainException
{
    public function __construct(TradeRouteId $id)
    {
        parent::__construct(sprintf('TradeRoute %s nicht gefunden.', $id));
    }
}
