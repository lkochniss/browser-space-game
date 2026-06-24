<?php

declare(strict_types=1);

namespace App\Battle\ValueObject;

enum BattleStatus: string
{
    case RUNNING = 'running';
    case ENDED_ATTACKER_WIN = 'ended_attacker_win';
    case ENDED_DEFENDER_WIN = 'ended_defender_win';
    case DRAW = 'draw';

    public function isEnded(): bool
    {
        return $this !== self::RUNNING;
    }
}
