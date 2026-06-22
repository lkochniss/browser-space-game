<?php

declare(strict_types=1);

namespace App\Tests\Player\Service;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Command\ColonizePlanetCommand;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

/**
 * T-096 Player-History-Stats Foundation:
 *  - buildingsBuilt hochzählt nach Build-Success
 *  - planetsColonized hochzählt nach Colonize-Success
 *  - shipsBuilt hochzählt nach Build-Ship-Success
 *
 * Battle-Counters + Mining-Total folgen in T-096b.
 */
final class PlayerStatsCountersTest extends IntegrationTestCase
{
    public function test_default_counters_are_zero(): void
    {
        $player = new Player(PlayerId::generate());

        self::assertSame(0, $player->getStatsBuildingsBuilt());
        self::assertSame(0, $player->getStatsPlanetsColonized());
        self::assertSame(0, $player->getStatsShipsBuilt());
    }

    public function test_building_built_increments_counter(): void
    {
        $planet = $this->seedPlanetWithResources();
        $player = $planet->getPlayer();

        self::assertSame(0, $player->getStatsBuildingsBuilt());

        self::getContainer()->get(CommandBusInterface::class)
            ->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));

        $this->em->clear();
        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertSame(1, $reloaded->getStatsBuildingsBuilt());
    }

    public function test_ship_built_increments_counter(): void
    {
        $planet = $this->seedPlanetWithResources();
        // Shipyard für Schiff-Build hinzufügen
        $shipyard = new Building(BuildingId::generate(), BuildingType::SHIPYARD, 1);
        $shipyard->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $planet->addBuilding($shipyard);
        $this->em->flush();
        $player = $planet->getPlayer();

        self::assertSame(0, $player->getStatsShipsBuilt());

        self::getContainer()->get(CommandBusInterface::class)
            ->dispatch(new BuildShipCommand($planet->getId(), ShipType::GENERIC));

        $this->em->clear();
        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertSame(1, $reloaded->getStatsShipsBuilt());
    }

    public function test_planet_colonized_increments_counter(): void
    {
        $player = new Player(PlayerId::generate());
        $home = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($home);
        $home->getPopulation()->grow(100);
        $home->getPopulation()->assign(50);

        $target = Planet::generatePlanet(PlanetId::generate());

        $ship = new Ship(id: ShipId::generate(), type: ShipType::COLONY_SHIP, populationAssigned: 50);
        $ship->setPlanet($home);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        $this->em->persist($player);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        self::assertSame(0, $player->getStatsPlanetsColonized());

        self::getContainer()->get(CommandBusInterface::class)
            ->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));

        $this->em->clear();
        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertSame(1, $reloaded->getStatsPlanetsColonized());
    }

    public function test_multiple_builds_stack(): void
    {
        $planet = $this->seedPlanetWithResources(amount: 5000);
        $player = $planet->getPlayer();
        $bus = self::getContainer()->get(CommandBusInterface::class);

        // Tier-0 Buildings ohne Research-Lock
        $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::WATER_TANK));

        $this->em->clear();
        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertSame(3, $reloaded->getStatsBuildingsBuilt());
    }

    private function seedPlanetWithResources(int $amount = 1000): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $amount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, $amount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COPPER_ORE, $amount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $amount));
        $planet->getPopulation()->grow(200);

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }
}
