<?php

declare(strict_types=1);

namespace App\Trade\Exception;

use App\Ship\ValueObject\ShipId;

final class ShipAlreadyBoundException extends \DomainException
{
    public function __construct(ShipId $shipId)
    {
        parent::__construct(sprintf(
            'Ship %s ist bereits an eine andere TradeRoute oder Fleet gebunden.',
            $shipId,
        ));
    }
}
