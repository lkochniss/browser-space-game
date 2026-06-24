<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipClass;

final class MissingShipyardLevelException extends \DomainException
{
    public function __construct(ShipClass $class, int $requiredLevel, int $actualLevel)
    {
        parent::__construct(sprintf(
            'Building %s requires SHIPYARD ≥ L%d (current %d)',
            $class->value,
            $requiredLevel,
            $actualLevel,
        ));
    }
}
