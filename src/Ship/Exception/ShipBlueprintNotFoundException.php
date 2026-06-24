<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipClass;

final class ShipBlueprintNotFoundException extends \DomainException
{
    public function __construct(ShipClass $class)
    {
        parent::__construct(sprintf('No ShipBlueprint registered for class "%s"', $class->value));
    }
}
