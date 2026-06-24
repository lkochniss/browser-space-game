<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipClass;

final class ShipClassResearchNotMetException extends \DomainException
{
    public function __construct(ShipClass $class, string $requiredSlug)
    {
        parent::__construct(sprintf(
            'Building %s requires research "%s" Lvl 1+',
            $class->value,
            $requiredSlug,
        ));
    }
}
