<?php

namespace App\Simulation\Scenario;

use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\GameState\Model\GameState;
use App\Player\Command\CreateNewPlayerCommand;
use App\Tick\Engine\TickEngine;
use App\Tick\Processor\ResourceProductionProcessor;
use DateInterval;
use DateTimeImmutable;

readonly class PlayerStartUpScenario
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ResourceProductionProcessor $resourceProductionProcessor
    ){
    }

    public function run(): void
    {
        $player = $this->commandBus->dispatch(
            new CreateNewPlayerCommand()
        );

        $gameStateClock = new AdjustableClock(new DateTimeImmutable('2025-01-01T00:00:00Z'));
        $simulationClock = new AdjustableClock(new DateTimeImmutable('2025-01-01T00:00:00Z'));

        $simulationClock->advance(new DateInterval('PT30M'));

        $gameState = new GameState($player, $gameStateClock);

        $intervalSeconds = 900;
        $tickEngine = new TickEngine(
            [
                $this->resourceProductionProcessor
            ],
            $intervalSeconds
        );

        $elapsed = $simulationClock->diffInSeconds($gameStateClock);
        while ($elapsed > 0) {
            $tickEngine->run($gameState);
            $gameStateClock->advance(new DateInterval('PT' . $tickEngine->getIntervalSeconds() . 'S'));
            $elapsed -= $tickEngine->getIntervalSeconds();
        }

//        $realtimeEngine = new RealtimeEngine(/* ProcessorCollection */);
//        $realtimeEngine->run($gameState, $simulationClock);

        $this->printSunmary($gameState);

    }

    private function printSunmary(GameState $gameState)
    {
        foreach ($gameState->getPlayer()->getPlanets() as $planet) {
            echo "Planet {$planet->getId()} hat Ressourcen:\n";
            foreach ($planet->getResources() as $res) {
                echo "- {$res->getType()->value}: {$res->getAmount()}\n";
            }
        }
    }
}
