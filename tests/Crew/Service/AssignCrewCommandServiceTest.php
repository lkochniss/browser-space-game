<?php

declare(strict_types=1);

namespace App\Tests\Crew\Service;

use App\Common\Interface\CommandBusInterface;
use App\Crew\Command\AssignCrewCommand;
use App\Crew\Command\UnassignCrewCommand;
use App\Crew\Exception\CrewNotIdleException;
use App\Crew\Exception\ShipAlreadyHasCaptainException;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class AssignCrewCommandServiceTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_idle_captain_assigns_to_ship(): void
    {
        [$crew, $ship] = $this->seedCrewAndShip(CrewStatus::IDLE);

        $this->bus->dispatch(new AssignCrewCommand($crew->getId(), $ship->getId()));

        $this->em->clear();
        $reloaded = $this->em->find(Crew::class, $crew->getId());
        self::assertSame(CrewStatus::ASSIGNED, $reloaded->getStatus());
        self::assertNotNull($reloaded->getAssignedShip());
        self::assertSame((string) $ship->getId(), (string) $reloaded->getAssignedShip()->getId());
    }

    public function test_non_idle_captain_throws(): void
    {
        [$crew, $ship] = $this->seedCrewAndShip(CrewStatus::TRAINING);

        $this->expectException(CrewNotIdleException::class);
        $this->bus->dispatch(new AssignCrewCommand($crew->getId(), $ship->getId()));
    }

    public function test_ship_with_existing_captain_throws(): void
    {
        [$crew1, $ship] = $this->seedCrewAndShip(CrewStatus::IDLE);
        $this->bus->dispatch(new AssignCrewCommand($crew1->getId(), $ship->getId()));

        // Zweiten IDLE-Captain demselben Player anlegen
        $player = $crew1->getOwner();
        $crew2 = new Crew(
            CrewId::generate(), $player, CrewType::CAPTAIN, CrewStatus::IDLE, 1, 0,
        );
        $this->em->persist($crew2);
        $this->em->flush();

        $this->expectException(ShipAlreadyHasCaptainException::class);
        $this->bus->dispatch(new AssignCrewCommand($crew2->getId(), $ship->getId()));
    }

    public function test_unassign_sets_idle(): void
    {
        [$crew, $ship] = $this->seedCrewAndShip(CrewStatus::IDLE);
        $this->bus->dispatch(new AssignCrewCommand($crew->getId(), $ship->getId()));

        $this->bus->dispatch(new UnassignCrewCommand($crew->getId()));

        $this->em->clear();
        $reloaded = $this->em->find(Crew::class, $crew->getId());
        self::assertSame(CrewStatus::IDLE, $reloaded->getStatus());
        self::assertNull($reloaded->getAssignedShip());
    }

    /**
     * @return array{Crew, Ship}
     */
    private function seedCrewAndShip(CrewStatus $crewStatus): array
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $ship = new Ship(id: ShipId::generate(), type: ShipType::GENERIC, populationAssigned: 0);
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        $crew = new Crew(
            CrewId::generate(), $player, CrewType::CAPTAIN, $crewStatus, 1, 0,
        );

        $this->em->persist($player);
        $this->em->persist($ship);
        $this->em->persist($crew);
        $this->em->flush();

        return [$crew, $ship];
    }
}
