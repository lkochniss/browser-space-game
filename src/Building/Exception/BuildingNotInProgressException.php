<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingId;
use DomainException;

/**
 * T-094b: Versuch, ein bereits fertiges Building zu canceln. Nur unfinished
 * Build/Upgrade-Jobs sind cancelbar (`finishedAt > now`).
 */
class BuildingNotInProgressException extends DomainException
{
    public function __construct(BuildingId $buildingId)
    {
        parent::__construct(sprintf(
            'Building %s ist bereits fertig — Cancel nur möglich solange Bau/Upgrade läuft.',
            $buildingId,
        ));
    }
}
