<?php

namespace App\Simulation\Scenario;

use App\Building\Model\Building;
use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Common\Service\AdjustableClock;
use App\GameState\Model\GameState;
use App\Planet\Model\Planet;
use App\Player\Model\Player;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\BuildingId;
use App\Resource\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceDepositId;
use App\Resource\ValueObject\ResourceId;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Engine\TickEngine;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use App\Tick\Processor\ResourceProductionProcessor;
use DateTimeImmutable;
use ValueObject\PlanetId;
use ValueObject\PlayerId;

class IronOreScenario
{
    public function run()
    {
        $player = new Player(PlayerId::generate(), []);

        $building = new Building(
            BuildingId::generate(),
            BuildingType::IRON_MINE,
            1
        );

        $resource = new Resource(ResourceId::generate(), ResourceType::IRON_ORE, 0);
        $resourceDeposit = new ResourceDeposit(ResourceDepositId::generate(), ResourceType::IRON_ORE, 0);

        $planet = new Planet(PlanetId::generate(), $player, [$building], [$resource], [$resourceDeposit]);
        $player->setPlanets([$planet]);

        $clock = new AdjustableClock(new DateTimeImmutable('2025-01-01T00:00:00Z'));

        $gameState = new GameState($player, $clock);

        $map = new ResourceBuildingMap();
        $processors = [
            new ResourceProductionProcessor(
                new BasicResourceExtractionPolicy($map),
                new ResourceBuildingMap(),
                new ResourceProductionConfig(),
                new ResourceProductionHelper($map)
            )
        ];

        $tickEngine = new TickEngine($processors);

        for ($i = 0; $i < 10; $i++) {
            $tickEngine->runTick($gameState);
            $clock->advance(seconds: 60);
        }

        foreach ($player->getPlanets() as $planet) {
            echo "Planet {$planet->getId()} hat Ressourcen:\n";
            foreach ($planet->getResources() as $res) {
                echo "- {$res->getType()->value}: {$res->getAmount()}\n";
            }
        }
    }
}
