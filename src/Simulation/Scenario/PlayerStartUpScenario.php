<?php

declare(strict_types=1);

namespace App\Simulation\Scenario;

use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\GameState\Model\GameState;
use App\Player\Command\CreateNewPlayerCommand;
use App\Tick\Engine\TickEngine;
use App\Tick\Processor\ConstructionCompletionProcessor;
use App\Tick\Processor\PopulationConsumptionProcessor;
use App\Tick\Processor\RefinementProductionProcessor;
use App\Tick\Processor\ResourceProductionProcessor;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class PlayerStartUpScenario
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ConstructionCompletionProcessor $constructionCompletionProcessor,
        private ResourceProductionProcessor $resourceProductionProcessor,
        private RefinementProductionProcessor $refinementProductionProcessor,
        private PopulationConsumptionProcessor $populationConsumptionProcessor,
        private EntityManagerInterface $em,
    ) {
    }

    public function run(): void
    {
        $player = $this->commandBus->dispatch(
            new CreateNewPlayerCommand()
        );

        // T-062: Game-Clock startet bei "jetzt" (Wall-Clock), damit beim Claim gesetzte
        // finishedAt-Werte konsistent zur Tick-Clock sind.
        $startedAt = new DateTimeImmutable();
        $gameStateClock = new AdjustableClock($startedAt);
        $simulationClock = new AdjustableClock($startedAt);

        $simulationClock->advance(new DateInterval('PT30M'));

        $gameState = new GameState($player, $gameStateClock);

        $intervalSeconds = 900;
        $tickEngine = new TickEngine(
            [
                $this->constructionCompletionProcessor,
                $this->resourceProductionProcessor,
                $this->refinementProductionProcessor,
                $this->populationConsumptionProcessor,
            ],
            $this->em,
            $intervalSeconds,
        );

        $elapsed = $simulationClock->diffInSeconds($gameStateClock);
        while ($elapsed > 0) {
            $tickEngine->run($gameState);
            $gameStateClock->advance(new DateInterval('PT' . $tickEngine->getIntervalSeconds() . 'S'));
            $elapsed -= $tickEngine->getIntervalSeconds();
        }

        $this->printSummary($gameState);
    }

    private function printSummary(GameState $gameState): void
    {
        foreach ($gameState->getPlayer()->getPlanets() as $planet) {
            $pop = $planet->getPopulation();
            echo "Planet {$planet->getId()} — Pop {$pop->getTotal()}/{$pop->getCap()} (free {$pop->getFree()})\n";
            foreach ($planet->getResources() as $res) {
                echo "- {$res->getType()->value}: {$res->getAmount()}\n";
            }
        }
    }
}
