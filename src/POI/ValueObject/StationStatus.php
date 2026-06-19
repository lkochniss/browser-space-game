<?php

declare(strict_types=1);

namespace App\POI\ValueObject;

/**
 * T-023 Raumstation-Status.
 *
 * - ACTIVE: Owner zahlt Maintenance, Pop lebt, Storage nutzbar
 * - ABANDONED: Maintenance-Failure (Pop tot oder owner=null) → andere Player
 *   können via T-023b ClaimAbandonedStationCommand übernehmen
 *
 * Maintenance-Tick + Übernahme-Mechanik sind Out-of-Scope für T-023 Foundation.
 */
enum StationStatus: string
{
    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
}
