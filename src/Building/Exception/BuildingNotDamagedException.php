<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingId;

final class BuildingNotDamagedException extends \DomainException
{
    public function __construct(BuildingId $buildingId)
    {
        parent::__construct(sprintf('Building %s ist nicht beschädigt; Repair nicht nötig.', $buildingId));
    }
}
