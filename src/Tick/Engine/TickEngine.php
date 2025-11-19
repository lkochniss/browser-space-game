<?php

namespace App\Tick\Engine;

use App\GameState\Model\GameState;
use App\Tick\Interface\TickProcessorInterface;

class TickEngine
{

    /**
     * @param iterable<TickProcessorInterface> $processors
     */
    public function __construct(private iterable $processors, private int $intervalSeconds = 900)
    {
    }

    public function run(GameState $gameState): void
    {
        $planets = $gameState->getPlayer()->getPlanets();

        foreach ($planets as $planet) {
            foreach ($this->processors as $processor) {
                $processor->process($planet);
            }
        }
    }

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }
}
