<?php

declare(strict_types=1);

namespace App\POI\ValueObject;

/**
 * T-019 POI-Subtypes. Discriminator-Werte für Single-Table-Inheritance.
 *
 * Folge-Tickets erweitern die DiscriminatorMap um konkrete Poi-Subklassen:
 * - T-020 Asteroidenfeld (ASTEROID_FIELD)
 * - T-021 Trümmerfeld (DEBRIS_FIELD)
 * - T-022 Nebel (NEBULA)
 * - T-023 Raumstation (STATION)
 * - T-074/T-075 Pirat-Encounters / Outposts (UNKNOWN_FLEET)
 * - T-085 Wurmloch (WORMHOLE)
 * - T-086 Schwarzes Loch (BLACK_HOLE)
 */
enum PoiType: string
{
    case DEBRIS_FIELD = 'debris_field';
    case NEBULA = 'nebula';
    case STATION = 'station';
    case UNKNOWN_FLEET = 'unknown_fleet';
    case ASTEROID_FIELD = 'asteroid_field';
    case WORMHOLE = 'wormhole';
    case BLACK_HOLE = 'black_hole';
}
