<?php

declare(strict_types=1);

namespace App\Tests\Fleet\Command;

use App\Common\Interface\CommandBusInterface;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Exception\FleetAlreadyInTransitException;
use App\Fleet\Exception\SameOriginAndTargetException;
use App\Fleet\Model\Fleet;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Exception\PlanetNotFoundException;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class MoveFleetCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_move_intra_system_uses_short_baseline(): void
    {
        [$fleet, , $target] = $this->seedFleetTwoPlanetsSameSystem(ShipType::GENERIC);
        $fleetId = $fleet->getId();

        $this->bus->dispatch(new MoveFleetCommand($fleetId, $target->getId()));

        $this->em->clear();
        $reloaded = self::getContainer()->get(FleetRepository::class)->find($fleetId);
        self::assertSame(FleetStatus::IN_TRANSIT, $reloaded->getStatus());
        self::assertNotNull($reloaded->getDepartedAt());
        self::assertNotNull($reloaded->getArrivedAt());

        // Intra-System / Speed 1.0 → 1800s = 30min
        $diff = $reloaded->getArrivedAt()->getTimestamp() - $reloaded->getDepartedAt()->getTimestamp();
        self::assertEqualsWithDelta(1800, $diff, 5);

        foreach ($reloaded->getShips() as $ship) {
            self::assertNull($ship->getPlanet(), 'ship should be in transit (planet=null)');
        }
    }

    public function test_move_inter_system_takes_longer(): void
    {
        [$fleet, , $target] = $this->seedFleetTwoSystems(ShipType::GENERIC);
        $fleetId = $fleet->getId();

        $this->bus->dispatch(new MoveFleetCommand($fleetId, $target->getId()));

        $this->em->clear();
        $reloaded = self::getContainer()->get(FleetRepository::class)->find($fleetId);

        // Inter-System / Speed 1.0 → 14400s = 4h
        $diff = $reloaded->getArrivedAt()->getTimestamp() - $reloaded->getDepartedAt()->getTimestamp();
        self::assertEqualsWithDelta(14400, $diff, 5);
    }

    public function test_slowest_ship_extends_duration(): void
    {
        [$fleet, , $target] = $this->seedFleetTwoPlanetsSameSystem(ShipType::TRANSPORT_LARGE);
        $fleetId = $fleet->getId();

        $this->bus->dispatch(new MoveFleetCommand($fleetId, $target->getId()));

        $this->em->clear();
        $reloaded = self::getContainer()->get(FleetRepository::class)->find($fleetId);

        // 1800s / 0.6 = 3000s
        $diff = $reloaded->getArrivedAt()->getTimestamp() - $reloaded->getDepartedAt()->getTimestamp();
        self::assertEqualsWithDelta(3000, $diff, 5);
    }

    public function test_throws_when_fleet_in_transit(): void
    {
        [$fleet, , $target] = $this->seedFleetTwoPlanetsSameSystem(ShipType::GENERIC);

        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), $target->getId()));

        $this->expectException(FleetAlreadyInTransitException::class);
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), $target->getId()));
    }

    public function test_throws_when_target_equals_origin(): void
    {
        [$fleet, $origin] = $this->seedFleetTwoPlanetsSameSystem(ShipType::GENERIC);

        $this->expectException(SameOriginAndTargetException::class);
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), $origin->getId()));
    }

    public function test_throws_when_target_planet_not_found(): void
    {
        [$fleet] = $this->seedFleetTwoPlanetsSameSystem(ShipType::GENERIC);

        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), PlanetId::generate()));
    }

    /**
     * @return array{Fleet, Planet, Planet}
     */
    private function seedFleetTwoPlanetsSameSystem(ShipType $shipType): array
    {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        $origin = Planet::generatePlanet(PlanetId::generate());
        $origin->setSolarSystem($system);
        $target = Planet::generatePlanet(PlanetId::generate());
        $target->setSolarSystem($system);

        $player->claimPlanet($origin);

        $ship = $this->makeReadyShip($shipType, $origin);

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));

        return [$fleet, $origin, $target];
    }

    /**
     * @return array{Fleet, Planet, Planet}
     */
    private function seedFleetTwoSystems(ShipType $shipType): array
    {
        $player = new Player(PlayerId::generate());
        $systemA = new SolarSystem(SolarSystemId::generate(), 'Sol-A');
        $systemB = new SolarSystem(SolarSystemId::generate(), 'Sol-B');

        $origin = Planet::generatePlanet(PlanetId::generate());
        $origin->setSolarSystem($systemA);
        $target = Planet::generatePlanet(PlanetId::generate());
        $target->setSolarSystem($systemB);

        $player->claimPlanet($origin);

        $ship = $this->makeReadyShip($shipType, $origin);

        $this->em->persist($player);
        $this->em->persist($systemA);
        $this->em->persist($systemB);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));

        return [$fleet, $origin, $target];
    }

    private function makeReadyShip(ShipType $type, Planet $planet): Ship
    {
        $ship = new Ship(
            id: ShipId::generate(),
            type: $type,
            populationAssigned: 20,
            cargoCapacity: 0,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        return $ship;
    }
}
