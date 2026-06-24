<?php

declare(strict_types=1);

namespace App\Ship\ValueObject;

/**
 * T-102 Stats-Schablone pro `ShipClass`. Immutable VO. Hardcoded in
 * `ShipBlueprintRegistry`.
 *
 * Mk II = Mk I × 1.5 Stats × 3× Cost; Mk III = Mk II × 1.5 × 3.
 */
final readonly class ShipBlueprint
{
    /**
     * @param array<string,int> $buildCost Map ResourceType.value → amount
     */
    public function __construct(
        public ShipClass $class,
        public int $hp,
        public int $damage,
        public int $shieldCapacity,
        public int $populationCost,
        public int $buildDurationSeconds,
        public array $buildCost,
        public int $escapePodChance,
        public bool $captainRequired = true,
    ) {
    }
}
