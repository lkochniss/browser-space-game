<?php

declare(strict_types=1);

namespace App\POI\Exception;

use DomainException;

/**
 * T-174: Station-Bau-Technologie ist im Universum verschollen (Lost-Tech-Lore).
 * Stations können nur via Claim (T-023b ABANDONED) oder Combat-Capture (T-176)
 * übernommen werden. Galaxy-Spawn (T-175) verteilt existierende Stations.
 */
final class StationConstructionDeprecatedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Station construction technology is lost — stations can only be claimed or captured (T-023b / T-176)',
        );
    }
}
