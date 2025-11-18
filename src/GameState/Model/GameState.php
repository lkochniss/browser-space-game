<?php

namespace App\GameState\Model;

use App\Common\Interface\ClockInterface;
use App\Player\Model\Player;

class GameState
{
    public function __construct(private Player $player, private ClockInterface $clock)
    {
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }
}
