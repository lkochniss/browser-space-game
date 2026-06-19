<?php

declare(strict_types=1);

namespace App\Building\Exception;

use DomainException;

/**
 * T-094 Bau-Queue: Foundation Parallel-Slot-Limit. Wird geworfen wenn Player
 * versucht einen weiteren Build/Upgrade auf einem Planeten zu starten, dessen
 * laufende Build/Upgrade-Jobs bereits den Slot-Cap erreicht haben.
 */
class BuildQueueFullException extends DomainException
{
    public function __construct(int $activeCount, int $maxSlots)
    {
        parent::__construct(sprintf(
            'Bau-Queue voll: %d von %d Slots belegt. Warte bis ein laufender Bau fertig ist.',
            $activeCount,
            $maxSlots,
        ));
    }
}
