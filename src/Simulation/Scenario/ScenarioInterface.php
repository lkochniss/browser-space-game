<?php

namespace App\Simulation\Scenario;

use App\Common\Interface\ClockInterface;
use App\GameState\Model\GameState;

interface ScenarioInterface
{
    public function run(GameState $state, ClockInterface $clock);
}
