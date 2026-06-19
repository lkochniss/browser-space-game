<?php

declare(strict_types=1);

namespace App\Tests\Fleet\Service;

use App\Common\Interface\CommandBusInterface;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\Service\FleetArrivalService;
use App\Fleet\ValueObject\FleetStatus;
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

final class FleetArrivalServiceTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;
    private FleetArrivalService $arrivalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
        $this->arrivalService = self::getContainer()->get(FleetArrivalService::class);
    }

    public function test_resolves_arrived_fleet_and_docks_ships_at_target(): void
    {
        [$fleet, , $target, $ship] = $this->seedFleetInTransit();

        // Backdate arrival to past to simulate "arrived"
        $fleet->setArrivedAt(new DateTimeImmutable('-1 minute'));
        $this->em->flush();

        $resolved = $this->arrivalService->resolveArrivedFleets();

        self::assertSame(1, $resolved);

        $this->em->clear();
        $reloadedFleet = self::getContainer()->get(FleetRepository::class)->find($fleet->getId());
        self::assertSame(FleetStatus::DOCKED, $reloadedFleet->getStatus());
        self::assertSame($target->getId()->__toString(), $reloadedFleet->getOriginPlanet()->getId()->__toString());
        self::assertNull($reloadedFleet->getTargetPlanet());

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertNotNull($reloadedShip->getPlanet());
        self::assertSame($target->getId()->__toString(), $reloadedShip->getPlanet()->getId()->__toString());
    }

    public function test_does_not_resolve_not_yet_arrived(): void
    {
        [$fleet, , , $ship] = $this->seedFleetInTransit();

        // arrivedAt liegt in der Zukunft (default)
        $resolved = $this->arrivalService->resolveArrivedFleets();

        self::assertSame(0, $resolved);

        $this->em->clear();
        $reloadedFleet = self::getContainer()->get(FleetRepository::class)->find($fleet->getId());
        self::assertSame(FleetStatus::IN_TRANSIT, $reloadedFleet->getStatus());

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertNull($reloadedShip->getPlanet(), 'ship still in transit');
    }

    /**
     * @return array{\App\Fleet\Model\Fleet, Planet, Planet, Ship}
     */
    private function seedFleetInTransit(): array
    {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        $origin = Planet::generatePlanet(PlanetId::generate());
        $origin->setSolarSystem($system);
        $target = Planet::generatePlanet(PlanetId::generate());
        $target->setSolarSystem($system);

        $player->claimPlanet($origin);

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::GENERIC,
            populationAssigned: 20,
            cargoCapacity: 0,
        );
        $ship->setPlanet($origin);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), $target->getId()));

        return [$fleet, $origin, $target, $ship];
    }
}
