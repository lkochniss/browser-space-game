<?php

declare(strict_types=1);

namespace App\Tick\Engine;

use App\GameState\Model\GameState;
use App\Tick\Interface\TickProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class TickEngine
{
    /**
     * @param iterable<TickProcessorInterface> $processors
     */
    public function __construct(
        private iterable $processors,
        private EntityManagerInterface $em,
        private int $intervalSeconds = 900,
    ) {
    }

    public function run(GameState $gameState): void
    {
        $now = $gameState->getClock()->now();

        $this->em->wrapInTransaction(function () use ($gameState, $now): void {
            foreach ($gameState->getPlayer()->getPlanets() as $planet) {
                foreach ($this->processors as $processor) {
                    $processor->process($planet, $now);
                }
            }
            $this->em->flush();
        });
    }

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }
}
