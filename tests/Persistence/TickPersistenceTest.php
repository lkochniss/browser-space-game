<?php

declare(strict_types=1);

namespace App\Tests\Persistence;

use App\Building\Model\Building;
use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\SystemClock;
use App\GameState\Model\GameState;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Service\PopulationConsumptionConfig;
use App\Resource\Service\RefinementConfig;
use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;
use App\Tick\Engine\TickEngine;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use App\Tick\Processor\PopulationConsumptionProcessor;
use App\Tick\Processor\RefinementProductionProcessor;
use App\Tick\Processor\ResourceProductionProcessor;

final class TickPersistenceTest extends IntegrationTestCase
{
    public function test_tick_mutations_persist_after_engine_run(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addBuilding(Building::createNewBuilding(BuildingType::IRON_MINE));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 1000));

        $this->em->persist($player);
        $this->em->flush();
        $playerId = $player->getId();
        $planetId = $planet->getId();
        $this->em->clear();

        $reloadedPlayer = $this->em->find(Player::class, $playerId);
        $gameState = new GameState($reloadedPlayer, new SystemClock());

        $engine = $this->buildEngine();
        $engine->run($gameState);

        $this->em->clear();

        $afterPlanet = $this->em->find(Planet::class, $planetId);
        self::assertSame(990, $afterPlanet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(10, $afterPlanet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_tick_run_is_atomic_per_engine_call(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addBuilding(Building::createNewBuilding(BuildingType::IRON_MINE));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 100));

        $this->em->persist($player);
        $this->em->flush();
        $planetId = $planet->getId();
        $this->em->clear();

        $reloadedPlayer = $this->em->find(Player::class, $player->getId());
        $gameState = new GameState($reloadedPlayer, new SystemClock());

        $engine = $this->buildEngine();
        // 3 ticks → 30 extracted
        $engine->run($gameState);
        $engine->run($gameState);
        $engine->run($gameState);

        $this->em->clear();

        $afterPlanet = $this->em->find(Planet::class, $planetId);
        self::assertSame(70, $afterPlanet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(30, $afterPlanet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_pop_consumption_persists_across_ticks(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::FOOD, 100));
        $planet->getPopulation()->grow(50);

        $this->em->persist($player);
        $this->em->flush();
        $planetId = $planet->getId();
        $this->em->clear();

        $reloadedPlayer = $this->em->find(Player::class, $player->getId());
        $gameState = new GameState($reloadedPlayer, new SystemClock());

        $engine = $this->buildEngineWithBothProcessors();
        $engine->run($gameState);

        $this->em->clear();

        $after = $this->em->find(Planet::class, $planetId);
        // Pop=50 → consume 5 water + 5 food, growth 3 (logistic)
        self::assertSame(95, $after->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(95, $after->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(53, $after->getPopulation()->getTotal());
    }

    public function test_refinement_persists_iron_bar_after_tick(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addBuilding(\App\Building\Model\Building::createNewBuilding(\App\Building\ValueObject\BuildingType::IRON_SMELTER));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));

        $this->em->persist($player);
        $this->em->flush();
        $planetId = $planet->getId();
        $this->em->clear();

        $reloadedPlayer = $this->em->find(Player::class, $player->getId());
        $gameState = new GameState($reloadedPlayer, new SystemClock());

        $engine = new TickEngine(
            [
                new RefinementProductionProcessor(new RefinementConfig()),
            ],
            $this->em,
            900,
        );
        $engine->run($gameState);

        $this->em->clear();

        $after = $this->em->find(Planet::class, $planetId);
        // L1 smelter: 1 bar from 2 iron + 1 coal
        self::assertSame(98, $after->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(99, $after->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(1, $after->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    private function buildEngine(): TickEngine
    {
        return new TickEngine([$this->buildProductionProcessor()], $this->em, 900);
    }

    private function buildEngineWithBothProcessors(): TickEngine
    {
        return new TickEngine(
            [
                $this->buildProductionProcessor(),
                new PopulationConsumptionProcessor(new PopulationConsumptionConfig()),
            ],
            $this->em,
            900,
        );
    }

    private function buildProductionProcessor(): ResourceProductionProcessor
    {
        $map = new ResourceBuildingMap();

        return new ResourceProductionProcessor(
            new BasicResourceExtractionPolicy($map),
            $map,
            new ResourceProductionConfig(),
            new ResourceProductionHelper($map),
        );
    }
}
