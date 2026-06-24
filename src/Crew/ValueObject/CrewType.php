<?php

declare(strict_types=1);

namespace App\Crew\ValueObject;

/**
 * T-104a Crew-Foundation: CAPTAIN nur. T-104c erweitert um ENGINEER + DIPLOMAT.
 */
enum CrewType: string
{
    case CAPTAIN = 'captain';

    /**
     * T-104a Training-Duration-Formel pro Crew-Type.
     * Captain: 60min × 2^count → erste schnell, später teuer.
     *
     * @param int $existingCount Anzahl bereits trainierter Crew dieses Typs beim Player
     */
    public function getTrainingDurationSeconds(int $existingCount): int
    {
        return match ($this) {
            self::CAPTAIN => 3600 * (2 ** max(0, $existingCount)),
        };
    }
}
