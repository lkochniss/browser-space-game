<?php

declare(strict_types=1);

namespace App\Battle\Service;

/**
 * T-103 Captain-Permadeath-Random-Roll. Injectable für Tests (mock 100=fail oder
 * 0=success).
 */
class BattleRandomizer
{
    /** Returns 0-99 inclusive. */
    public function roll(): int
    {
        return random_int(0, 99);
    }
}
