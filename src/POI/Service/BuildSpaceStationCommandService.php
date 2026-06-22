<?php

declare(strict_types=1);

namespace App\POI\Service;

use App\POI\Exception\StationConstructionDeprecatedException;
use App\POI\Model\SpaceStation;
use App\Player\ValueObject\PlayerId;
use App\SolarSystem\ValueObject\SolarSystemId;

/**
 * T-174: Soft-Deprecated. Station-Bau-Technologie ist verschollen (Lost-Tech-
 * Lore). Service wirft immer {@see StationConstructionDeprecatedException}.
 *
 * Stations gibt es nur über:
 * - Galaxy-Bootstrap-Spawn (T-175, pirate-owned/abandoned)
 * - Claim-ABANDONED (T-023b)
 * - Combat-Capture (T-176)
 *
 * Command/Handler/Service bleiben als Stub erhalten bis T-175 in Place ist —
 * danach kann der gesamte Build-Path hart entfernt werden.
 */
readonly class BuildSpaceStationCommandService
{
    public function __invoke(PlayerId $playerId, SolarSystemId $systemId): SpaceStation
    {
        throw new StationConstructionDeprecatedException();
    }
}
