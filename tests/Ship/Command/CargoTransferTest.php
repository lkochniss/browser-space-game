<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Command\LoadCargoCommand;
use App\Ship\Command\UnloadCargoCommand;
use App\Ship\Exception\CargoCapacityExceededException;
use App\Ship\Exception\InsufficientCargoException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\NotATransportShipException;
use App\Ship\Exception\ShipNotDockedException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class CargoTransferTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_build_transport_small_sets_cargo_capacity(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50);

        $ship = $this->bus->dispatch(new BuildShipCommand($planet->getId(), ShipType::TRANSPORT_SMALL));

        self::assertSame(ShipType::TRANSPORT_SMALL, $ship->getType());
        self::assertSame(1000, $ship->getCargoCapacity());
        self::assertSame(0, $ship->getCargo()->getTotalUnits());
    }

    public function test_load_resources_into_transport(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 500);

        $loaded = $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 200],
        ));

        self::assertSame(200, $loaded->getCargo()->getResource(ResourceType::IRON_BAR));

        $this->em->clear();
        $reloaded = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertSame(200, $reloaded->getCargo()->getResource(ResourceType::IRON_BAR));
        self::assertSame(300, $reloaded->getPlanet()->getResource(ResourceType::IRON_BAR)->getAmount());
    }

    public function test_load_pop_assigns_on_planet_and_loads_into_ship(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 100, popTotal: 50);
        $home = $ship->getPlanet();

        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            popCount: 10,
        ));

        $this->em->clear();
        $reloadedHome = $this->em->find(Planet::class, $home->getId());
        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());

        self::assertSame(10, $reloadedShip->getCargo()->getPopCount());
        // Pop wurde am Heimatplanet assigned (zusätzlich zur Schiff-Crew, die schon assigned war beim Build)
        self::assertSame(10 + $reloadedShip->getPopulationAssigned(), $reloadedHome->getPopulation()->getAssigned());
    }

    public function test_load_rejects_when_cargo_capacity_exceeded(): void
    {
        // TRANSPORT_SMALL hat 1000 capacity. Wir laden 1001.
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 5000);

        $this->expectException(CargoCapacityExceededException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 1001],
        ));
    }

    public function test_load_rejects_when_planet_resource_insufficient(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 50);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 100],
        ));
    }

    public function test_load_rejects_for_non_transport(): void
    {
        // Build GENERIC (non-Transport) instead
        $home = $this->seedPlanet(ironBar: 200, popTotal: 50);
        $ship = $this->bus->dispatch(new BuildShipCommand($home->getId(), ShipType::GENERIC));
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->expectException(NotATransportShipException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 10],
        ));
    }

    public function test_load_rejects_when_ship_not_ready(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 200, ready: false);

        $this->expectException(ShipNotReadyException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 10],
        ));
    }

    public function test_unload_after_arrival_transfers_resources_to_target_planet(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 500);

        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 300],
        ));

        // Target-Planet (un-claimed)
        $target = Planet::generatePlanet(PlanetId::generate());
        $this->em->persist($target);
        $this->em->flush();

        // T-017: Movement geschieht via Fleet. Hier simulieren wir die Ankunft
        // direkt durch ship.setPlanet — wirklicher Fleet-Flow ist in T-017 Tests.
        $ship->setPlanet($target);
        $this->em->flush();

        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 300],
        ));

        $this->em->clear();
        $reloadedTarget = $this->em->find(Planet::class, $target->getId());
        self::assertSame(300, $reloadedTarget->getResource(ResourceType::IRON_BAR)->getAmount());

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertSame(0, $reloadedShip->getCargo()->getTotalUnits());
        self::assertSame($target->getId()->__toString(), $reloadedShip->getPlanet()->getId()->__toString());
    }

    public function test_unload_rejects_when_cargo_insufficient(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 200);

        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 50],
        ));

        $this->expectException(InsufficientCargoException::class);
        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 100],
        ));
    }

    public function test_load_throws_when_ship_not_docked(): void
    {
        $ship = $this->seedReadyTransport(ironBarOnPlanet: 200);
        $ship->setPlanet(null);
        $this->em->flush();

        $this->expectException(ShipNotDockedException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 10],
        ));
    }

    private function seedPlanet(int $ironBar, int $popTotal): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $ironBar));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 1));

        if ($popTotal > 0) {
            $planet->getPopulation()->grow($popTotal);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }

    private function seedReadyTransport(
        int $ironBarOnPlanet = 200,
        int $popTotal = 50,
        bool $ready = true,
    ): Ship {
        $home = $this->seedPlanet(ironBar: $ironBarOnPlanet, popTotal: $popTotal);

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::TRANSPORT_SMALL,
            populationAssigned: 15,
            cargoCapacity: 1000,
        );
        $ship->setPlanet($home);
        $ship->setFinishedAt(
            $ready
                ? new DateTimeImmutable('-1 hour')
                : new DateTimeImmutable('+1 hour'),
        );

        // 15 Pop assigned (Schiff-Crew bei Build)
        if ($home->getPopulation()->getFree() >= 15) {
            $home->getPopulation()->assign(15);
        }

        $this->em->persist($ship);
        $this->em->flush();

        return $ship;
    }
}
