<?php

declare(strict_types=1);

namespace App\Battle\ValueObject;

/**
 * T-068 Snapshot der Planet-Defense-Stats für T-103 Battle-Resolver-Konsum.
 *
 * `shieldHpMax` = Σ(`PLANETARY_SHIELD.level × 5000`) über alle operational
 * PLANETARY_SHIELD-Instanzen (heute strikt-unique → entweder 0 oder 1 Stack).
 *
 * `shieldHp` = aktuelle Shield-Pool-HP (Live-Sum analog `currentHp` der
 * Shield-Buildings). T-103 wird sie pro Battle-Round runterzählen und
 * `Building::takeDamage()` syncen.
 */
final readonly class DefenseStats
{
    public function __construct(
        public int $shieldHp,
        public int $shieldHpMax,
        public int $turretDamage,
        public int $sensorRange,
        public int $aaDamage,
    ) {
    }

    public static function empty(): self
    {
        return new self(0, 0, 0, 0, 0);
    }
}
