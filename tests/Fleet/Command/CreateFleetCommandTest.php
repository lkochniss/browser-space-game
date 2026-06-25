<?php

declare(strict_types=1);

namespace App\Tests\Fleet\Command;

use App\Common\Interface\CommandBusInterface;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Exception\EmptyFleetException;
use App\Fleet\Exception\InvalidFleetCompositionException;
use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class CreateFleetCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_create_fleet_with_single_ship(): void
    {
        [$player, $planet, $ship] = $this->seedReadyShipOnPlanet(ShipType::GENERIC);

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));

        self::assertInstanceOf(Fleet::class, $fleet);
        self::assertSame(FleetStatus::DOCKED, $fleet->getStatus());
        self::assertSame($player->getId()->__toString(), $fleet->getPlayer()->getId()->__toString());
        self::assertSame($planet->getId()->__toString(), $fleet->getOriginPlanet()->getId()->__toString());

        $this->em->refresh($ship);
        self::assertNotNull($ship->getFleet());
        self::assertSame($fleet->getId()->__toString(), $ship->getFleet()->getId()->__toString());
    }

    public function test_create_fleet_with_multiple_ships(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $ship1 = $this->makeReadyShip(ShipType::GENERIC, $planet, populationAssigned: 20);
        $ship2 = $this->makeReadyShip(ShipType::TRANSPORT_LARGE, $planet, populationAssigned: 100, cargoCapacity: 20000);

        $this->em->persist($player);
        $this->em->persist($ship1);
        $this->em->persist($ship2);
        $this->em->flush();

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship1->getId(), $ship2->getId()]));

        self::assertCount(2, $fleet->getShips());
        // langsamstes Schiff bestimmt: TRANSPORT_LARGE 0.6
        self::assertSame(0.6, $fleet->getMinSpeed());
    }

    public function test_throws_when_ship_list_empty(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        $this->expectException(EmptyFleetException::class);
        $this->bus->dispatch(new CreateFleetCommand($player->getId(), []));
    }

    public function test_throws_when_ships_on_different_planets(): void
    {
        $player = new Player(PlayerId::generate());
        $planetA = Planet::generatePlanet(PlanetId::generate());
        $planetB = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planetA);
        $player->claimPlanet($planetB);

        $shipA = $this->makeReadyShip(ShipType::GENERIC, $planetA);
        $shipB = $this->makeReadyShip(ShipType::GENERIC, $planetB);

        $this->em->persist($player);
        $this->em->persist($shipA);
        $this->em->persist($shipB);
        $this->em->flush();

        $this->expectException(InvalidFleetCompositionException::class);
        $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$shipA->getId(), $shipB->getId()]));
    }

    public function test_throws_when_ship_not_ready(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $ship = $this->makeReadyShip(ShipType::GENERIC, $planet, ready: false);

        $this->em->persist($player);
        $this->em->persist($ship);
        $this->em->flush();

        $this->expectException(InvalidFleetCompositionException::class);
        $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));
    }

    public function test_throws_when_ship_already_in_fleet(): void
    {
        [$player, , $ship] = $this->seedReadyShipOnPlanet(ShipType::GENERIC);

        $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));

        $this->expectException(InvalidFleetCompositionException::class);
        $this->bus->dispatch(new CreateFleetCommand($player->getId(), [$ship->getId()]));
    }

    public function test_throws_when_ship_belongs_to_other_player(): void
    {
        $owner = new Player(PlayerId::generate());
        $other = new Player(PlayerId::generate());

        $planet = Planet::generatePlanet(PlanetId::generate());
        $other->claimPlanet($planet);

        $ship = $this->makeReadyShip(ShipType::GENERIC, $planet);

        $this->em->persist($owner);
        $this->em->persist($other);
        $this->em->persist($ship);
        $this->em->flush();

        $this->expectException(InvalidFleetCompositionException::class);
        $this->bus->dispatch(new CreateFleetCommand($owner->getId(), [$ship->getId()]));
    }

    /**
     * @return array{Player, Planet, Ship}
     */
    private function seedReadyShipOnPlanet(ShipType $shipType): array
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $ship = $this->makeReadyShip($shipType, $planet);

        $this->em->persist($player);
        $this->em->persist($ship);
        $this->em->flush();

        return [$player, $planet, $ship];
    }

    private function makeReadyShip(
        ShipType $type,
        Planet $planet,
        int $populationAssigned = 20,
        int $cargoCapacity = 0,
        bool $ready = true,
    ): Ship {
        $ship = new Ship(
            id: ShipId::generate(),
            type: $type,
            populationAssigned: $populationAssigned,
            cargoVolumeCapacity: $cargoCapacity,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(
            $ready
                ? new DateTimeImmutable('-1 hour')
                : new DateTimeImmutable('+1 hour'),
        );

        return $ship;
    }
}
