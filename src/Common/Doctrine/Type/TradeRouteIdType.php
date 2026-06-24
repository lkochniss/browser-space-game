<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Trade\ValueObject\TradeRouteId;

final class TradeRouteIdType extends AbstractUuidType
{
    public const NAME = 'trade_route_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return TradeRouteId::class;
    }
}
