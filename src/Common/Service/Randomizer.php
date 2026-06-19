<?php

declare(strict_types=1);

namespace App\Common\Service;

/**
 * Wrapper um `random_int` damit Tests deterministisches Verhalten injecten können.
 *
 * Default: nutzt PHP's CSPRNG. Tests können `FixedSequenceRandomizer` (oder eigene
 * Stub-Implementierung) injecten.
 */
class Randomizer
{
    public function intBetween(int $min, int $max): int
    {
        if ($min > $max) {
            throw new \InvalidArgumentException(sprintf('min (%d) > max (%d)', $min, $max));
        }

        return random_int($min, $max);
    }

    /**
     * Würfelt 1..100 und gibt true wenn ≤ $percent.
     */
    public function chancePercent(int $percent): bool
    {
        if ($percent <= 0) {
            return false;
        }
        if ($percent >= 100) {
            return true;
        }

        return $this->intBetween(1, 100) <= $percent;
    }
}
