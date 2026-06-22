<?php

declare(strict_types=1);

namespace App\Planet\Exception;

use DomainException;

/**
 * T-101: Player hat sein Planet-Cap erreicht und kann keinen weiteren Planeten
 * kolonisieren. Cap = `PlayerPlanetCapCalculator` (Base 5 + logistics_1, Hard
 * Cap 10).
 */
final class PlanetCapReachedException extends DomainException
{
    public function __construct(public readonly int $current, public readonly int $cap)
    {
        parent::__construct(sprintf(
            'Planet-Cap erreicht (%d/%d) — Logistics-Forschung erhöht das Cap.',
            $current,
            $cap,
        ));
    }
}
