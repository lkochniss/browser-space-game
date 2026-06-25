<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\POI\Model\SpaceStation;
use App\POI\ValueObject\PoiId;
use App\POI\ValueObject\StationStatus;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\LoadCargoCommand;
use App\Ship\Command\UnloadCargoCommand;
use App\Ship\Exception\CargoCapacityExceededException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\ShipNotDockedException;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class StationCargoTransferTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_load_cargo_from_station(): void
    {
        [$ship, $station] = $this->seedShipDockedAtStation(stationIronBar: 500);

        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 150],
        ));

        self::assertSame(150, $ship->getCargo()->getResource(ResourceType::IRON_BAR));
        self::assertSame(350, $station->getStorage()->getResource(ResourceType::IRON_BAR));
    }

    public function test_unload_cargo_to_station(): void
    {
        [$ship, $station] = $this->seedShipDockedAtStation(shipIronBar: 200);

        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 80],
        ));

        self::assertSame(120, $ship->getCargo()->getResource(ResourceType::IRON_BAR));
        self::assertSame(80, $station->getStorage()->getResource(ResourceType::IRON_BAR));
    }

    public function test_load_more_than_station_has_throws(): void
    {
        [$ship] = $this->seedShipDockedAtStation(stationIronBar: 50);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 200],
        ));
    }

    public function test_unload_exceeds_station_capacity_throws(): void
    {
        // Ship hat 200 IRON_BAR im Cargo, Station nur 100 Storage frei → Unload 200 wirft.
        [$ship, $station] = $this->seedShipDockedAtStation(shipIronBar: 200, stationCapacity: 100);

        $this->expectException(CargoCapacityExceededException::class);
        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 200],
        ));
    }

    public function test_t015c_load_pop_from_station(): void
    {
        // Station hat 50 Pop, Ship lädt 20 ab → Station 30, Ship-Cargo +20 Pop.
        [$ship, $station] = $this->seedShipDockedAtStation(stationPop: 50);

        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            popCount: 20,
        ));

        self::assertSame(20, $ship->getCargo()->getPopCount());
        self::assertSame(30, $station->getPopulationOnStation());
    }

    public function test_t015c_load_more_pop_than_station_has_throws(): void
    {
        [$ship] = $this->seedShipDockedAtStation(stationPop: 5);

        $this->expectException(\App\Ship\Exception\InsufficientPopulationException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            popCount: 20,
        ));
    }

    public function test_t015c_unload_pop_to_station(): void
    {
        // Ship hat 30 Pop im Cargo, Unload 25 → Station +25 Pop.
        [$ship, $station] = $this->seedShipDockedAtStation(stationPop: 10);
        $ship->loadPopCargo(30);
        $this->em->flush();

        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            popCount: 25,
        ));

        self::assertSame(5, $ship->getCargo()->getPopCount());
        self::assertSame(35, $station->getPopulationOnStation());
    }

    public function test_undocked_ship_cannot_load(): void
    {
        $ship = $this->seedUndockedShip();

        $this->expectException(ShipNotDockedException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::IRON_BAR->value => 10],
        ));
    }

    /**
     * @return array{0: Ship, 1: SpaceStation}
     */
    private function seedShipDockedAtStation(
        int $stationIronBar = 0,
        int $shipIronBar = 0,
        int $stationCapacity = 100000,
        int $stationPop = 0,
    ): array {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $system->addPlanet($planet);

        $station = new SpaceStation(
            id: PoiId::generate(),
            solarSystem: $system,
            owner: $player,
            name: 'Test-Station',
            populationOnStation: $stationPop,
            storageCapacity: $stationCapacity,
        );
        if ($stationIronBar > 0) {
            $station->getStorage()->loadResource(ResourceType::IRON_BAR, $stationIronBar);
        }
        $system->addPoi($station);

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::TRANSPORT_SMALL,
            populationAssigned: 0,
            cargoVolumeCapacity: 1000,
        );
        $ship->setStation($station);
        if ($shipIronBar > 0) {
            $ship->loadResourceCargo(ResourceType::IRON_BAR, $shipIronBar);
        }

        $this->em->persist($system);
        $this->em->persist($player);
        $this->em->persist($station);
        $this->em->persist($ship);
        $this->em->flush();

        return [$ship, $station];
    }

    private function seedUndockedShip(): Ship
    {
        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::TRANSPORT_SMALL,
            populationAssigned: 0,
            cargoVolumeCapacity: 1000,
        );
        // Weder planet noch station gesetzt → undocked.
        $this->em->persist($ship);
        $this->em->flush();

        return $ship;
    }
}
