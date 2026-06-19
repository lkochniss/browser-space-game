<?php

declare(strict_types=1);

namespace App\Fleet\ValueObject;

enum FleetStatus: string
{
    case DOCKED = 'docked';
    case IN_TRANSIT = 'in_transit';
}
