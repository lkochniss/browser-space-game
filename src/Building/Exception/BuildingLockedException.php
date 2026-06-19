<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingType;
use DomainException;

/**
 * T-170: Building ist via Tech-Tree-Gating (Forschung) gesperrt und kann
 * vom Player nicht gebaut werden.
 */
class BuildingLockedException extends DomainException
{
    public function __construct(BuildingType $type, string $requiredSlug, int $requiredLevel)
    {
        parent::__construct(sprintf(
            'Building %s ist gesperrt — erfordert Forschung "%s" auf Level %d.',
            $type->value,
            $requiredSlug,
            $requiredLevel,
        ));
    }
}
