<?php

declare(strict_types=1);

namespace App\Tests\Fleet\Command;

use App\Common\Interface\CommandBusInterface;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Command\DisbandFleetCommand;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Exception\FleetAlreadyInTransitException;
use App\Fleet\Exception\FleetNotFoundException;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetId;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class DisbandFleetCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_disband_docked_fleet_unsets_ship_fleet(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $ship = $this->makeReadyShip($planet);

        $this->em->persist($player);
        $this->em->persist($ship);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));
        $fleetId = $fleet->getId();

        $this->bus->dispatch(new DisbandFleetCommand($fleetId));

        $this->em->clear();
        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertNull($reloadedShip->getFleet());

        $reloadedFleet = self::getContainer()->get(FleetRepository::class)->find($fleetId);
        self::assertNull($reloadedFleet);
    }

    public function test_throws_when_fleet_in_transit(): void
    {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol');

        $origin = Planet::generatePlanet(PlanetId::generate());
        $origin->setSolarSystem($system);
        $target = Planet::generatePlanet(PlanetId::generate());
        $target->setSolarSystem($system);

        $player->claimPlanet($origin);

        $ship = $this->makeReadyShip($origin);

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), $target->getId()));

        $this->expectException(FleetAlreadyInTransitException::class);
        $this->bus->dispatch(new DisbandFleetCommand($fleet->getId()));
    }

    public function test_throws_when_fleet_not_found(): void
    {
        $this->expectException(FleetNotFoundException::class);
        $this->bus->dispatch(new DisbandFleetCommand(FleetId::generate()));
    }

    private function makeReadyShip(Planet $planet): Ship
    {
        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::GENERIC,
            populationAssigned: 20,
            cargoCapacity: 0,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        return $ship;
    }
}
