<?php

declare(strict_types=1);

namespace App\Tests\Persistence;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class PlayerPlanetPersistenceTest extends IntegrationTestCase
{
    public function test_persist_and_reload_full_aggregate(): void
    {
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();

        $player = new Player($playerId);
        $planet = Planet::generatePlanet($planetId);
        $player->claimPlanet($planet);
        $planet->addBuilding(Building::createNewBuilding(BuildingType::IRON_MINE));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 1000));

        $this->em->persist($player);
        $this->em->flush();
        $this->em->clear();

        $loaded = $this->em->find(Player::class, $playerId);
        self::assertNotNull($loaded);
        self::assertCount(1, $loaded->getPlanets());

        $loadedPlanet = $loaded->getPlanets()->first();
        self::assertTrue($loadedPlanet->getId()->equals($planetId));
        self::assertSame($loaded, $loadedPlanet->getPlayer());

        self::assertCount(1, $loadedPlanet->getBuildings());
        self::assertSame(BuildingType::IRON_MINE, $loadedPlanet->getBuildings()->first()->getType());
        self::assertSame(1, $loadedPlanet->getBuildings()->first()->getLevel());

        self::assertSame(0, $loadedPlanet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(1000, $loadedPlanet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());

        $pop = $loadedPlanet->getPopulation();
        self::assertSame(0, $pop->getTotal());
        self::assertSame(0, $pop->getAssigned());
        self::assertSame(100, $pop->getCap());
    }

    public function test_hub_raises_cap_and_persists(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addBuilding(\App\Building\Model\Building::createNewBuilding(\App\Building\ValueObject\BuildingType::HUB));
        $planet->getPopulation()->grow(120);

        $this->em->persist($player);
        $this->em->flush();
        $planetId = $planet->getId();
        $this->em->clear();

        $loaded = $this->em->find(Planet::class, $planetId);
        self::assertSame(150, $loaded->getPopulation()->getCap());
        self::assertSame(120, $loaded->getPopulation()->getTotal());
        self::assertSame(\App\Building\ValueObject\BuildingType::HUB, $loaded->getBuildings()->first()->getType());
    }

    public function test_population_mutations_persist(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->getPopulation()->grow(50);
        $planet->getPopulation()->assign(20);

        $this->em->persist($player);
        $this->em->flush();
        $planetId = $planet->getId();
        $this->em->clear();

        $loaded = $this->em->find(Planet::class, $planetId);
        self::assertSame(50, $loaded->getPopulation()->getTotal());
        self::assertSame(20, $loaded->getPopulation()->getAssigned());
        self::assertSame(30, $loaded->getPopulation()->getFree());
    }

    public function test_mutations_persist_through_flush(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 1000));

        $this->em->persist($player);
        $this->em->flush();

        $planet->getResource(ResourceType::IRON_ORE)->setAmount(50);
        $planet->getResourceDeposit(ResourceType::IRON_ORE)->setAmount(950);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->find(Planet::class, $planet->getId());
        self::assertSame(50, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(950, $reloaded->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
    }
}
