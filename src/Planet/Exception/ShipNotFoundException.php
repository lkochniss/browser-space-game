<?php

declare(strict_types=1);

namespace App\Planet\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class ShipNotFoundException extends DomainException
{
    public function __construct(public readonly ShipId $shipId)
    {
        parent::__construct(sprintf('Ship "%s" not found', $shipId));
    }
}
