<?php

declare(strict_types=1);

namespace App\Trade\ValueObject;

/**
 * T-110 Trip-Leg-Tracker für TradeRouteProcessor State-Machine.
 *
 *   AT_SOURCE → GOING_TO_TARGET → AT_TARGET → GOING_TO_SOURCE → AT_SOURCE → ...
 *
 * Single-Trip ohne `return*`: nach AT_TARGET wird die Route auf CANCELLED gesetzt.
 */
enum TradeRouteLeg: string
{
    case AT_SOURCE = 'at_source';
    case GOING_TO_TARGET = 'going_to_target';
    case AT_TARGET = 'at_target';
    case GOING_TO_SOURCE = 'going_to_source';
}
