<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipId;
use DomainException;

final class InsufficientCargoException extends DomainException
{
    public function __construct(
        public readonly ShipId $shipId,
        public readonly string $what,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Cannot unload %d %s from ship "%s": only %d in cargo',
            $requested,
            $what,
            $shipId,
            $available,
        ));
    }
}
