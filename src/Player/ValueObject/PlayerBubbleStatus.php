<?php

declare(strict_types=1);

namespace App\Player\ValueObject;

/**
 * T-150: Anti-Crush-Tutorial-Phase pro Player.
 *
 * - `BUBBLE` (Default bei Player-Create): Newbie-Schutz. Dienen als Hook für
 *   `PirateSpawnService` (T-074), `OutpostAttacks` (T-075), `AuctionService`
 *   (T-111) etc. — alle dortigen Services überspringen BUBBLE-Player.
 * - `EXITED`: Player hat den 2. Planeten kolonisiert → volle Game-Mechanik.
 *
 * Trigger für den Übergang BUBBLE → EXITED: `ColonizePlanetCommandService`
 * setzt den Status automatisch wenn der Player nach Erfolg >= 2 Planeten
 * besitzt.
 */
enum PlayerBubbleStatus: string
{
    case BUBBLE = 'bubble';
    case EXITED = 'exited';

    public function isBubble(): bool
    {
        return $this === self::BUBBLE;
    }
}
