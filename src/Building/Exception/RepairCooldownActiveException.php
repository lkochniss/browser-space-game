<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingId;

final class RepairCooldownActiveException extends \DomainException
{
    public function __construct(BuildingId $buildingId, int $remainingSeconds)
    {
        parent::__construct(sprintf(
            'Repair-Cooldown auf Building %s noch %d Sekunden aktiv.',
            $buildingId,
            $remainingSeconds,
        ));
    }
}
