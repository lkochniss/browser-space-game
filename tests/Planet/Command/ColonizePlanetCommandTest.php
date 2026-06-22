<?php

declare(strict_types=1);

namespace App\Tests\Planet\Command;

use App\Common\Interface\CommandBusInterface;
use App\Planet\Command\ColonizePlanetCommand;
use App\Planet\Exception\ColonyShipNotDockedException;
use App\Planet\Exception\NotAColonyShipException;
use App\Planet\Exception\PlanetAlreadyClaimedException;
use App\Planet\Exception\PlanetCapReachedException;
use App\Planet\Exception\PlanetNotFoundException;
use App\Player\ValueObject\PlayerBubbleStatus;
use App\Planet\Exception\ShipNotFoundException;
use App\Planet\Exception\ShipNotReadyException;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class ColonizePlanetCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_colonize_succeeds_consumes_ship_and_transfers_pop(): void
    {
        [$player, $home, $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::COLONY_SHIP,
            shipReady: true,
        );

        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));

        $this->em->clear();

        // Heimat: -50 assigned + -50 total (Pop verließ den Planeten)
        $reloadedHome = $this->em->find(Planet::class, $home->getId());
        self::assertSame(50, $reloadedHome->getPopulation()->getTotal());
        self::assertSame(0, $reloadedHome->getPopulation()->getAssigned());

        // Target: claimed + Start-Pop 50
        $reloadedTarget = $this->em->find(Planet::class, $target->getId());
        self::assertNotNull($reloadedTarget->getPlayer());
        self::assertSame($player->getId()->__toString(), $reloadedTarget->getPlayer()->getId()->__toString());
        self::assertSame(50, $reloadedTarget->getPopulation()->getTotal());

        // Ship: gelöscht
        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertNull($repo->find($ship->getId()));
    }

    public function test_throws_when_ship_not_found(): void
    {
        $this->expectException(ShipNotFoundException::class);
        $this->bus->dispatch(new ColonizePlanetCommand(ShipId::generate(), PlanetId::generate()));
    }

    public function test_throws_when_ship_is_not_colony_ship(): void
    {
        [, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::GENERIC,
            shipReady: true,
        );

        $this->expectException(NotAColonyShipException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
    }

    public function test_throws_when_ship_not_ready(): void
    {
        [, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::COLONY_SHIP,
            shipReady: false,
        );

        $this->expectException(ShipNotReadyException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
    }

    public function test_throws_when_target_planet_not_found(): void
    {
        [, , , $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::COLONY_SHIP,
            shipReady: true,
        );

        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), PlanetId::generate()));
    }

    public function test_throws_when_target_already_claimed(): void
    {
        [, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::COLONY_SHIP,
            shipReady: true,
        );

        // Anderen Player setzen
        $other = new Player(PlayerId::generate());
        $other->claimPlanet($target);
        $this->em->persist($other);
        $this->em->flush();

        $this->expectException(PlanetAlreadyClaimedException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
    }

    public function test_throws_when_ship_has_no_home_planet(): void
    {
        [, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::COLONY_SHIP,
            shipReady: true,
        );

        // Detache Schiff vom Heimat-Planet
        $ship->setPlanet(null);
        $this->em->flush();

        $this->expectException(ColonyShipNotDockedException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
    }

    public function test_bubble_status_exits_on_second_planet(): void
    {
        // T-150: Player startet in BUBBLE; nach 2. Planeten → EXITED
        [$player, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: \App\Ship\ValueObject\ShipType::COLONY_SHIP,
            shipReady: true,
        );
        self::assertTrue($player->isInBubble(), 'Player startet in BUBBLE');

        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));

        $this->em->clear();
        $reloaded = $this->em->find(\App\Player\Model\Player::class, $player->getId());
        self::assertSame(PlayerBubbleStatus::EXITED, $reloaded->getBubbleStatus());
    }

    public function test_throws_when_player_planet_cap_reached(): void
    {
        // T-101: Player auf Cap (5 Planeten ohne Logistics-Forschung) → cap-violation
        [$player, , $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: \App\Ship\ValueObject\ShipType::COLONY_SHIP,
            shipReady: true,
        );
        // Player hat schon 1 Planet (Heimat) — claim weitere 4 → 5/5
        for ($i = 0; $i < 4; $i++) {
            $extra = Planet::generatePlanet(PlanetId::generate());
            $player->claimPlanet($extra);
            $this->em->persist($extra);
        }
        $this->em->flush();

        $this->expectException(PlanetCapReachedException::class);
        $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
    }

    public function test_no_state_change_on_validation_failure(): void
    {
        [, $home, $target, $ship] = $this->seed(
            popHomeTotal: 100,
            popHomeAssigned: 50,
            shipType: ShipType::GENERIC, // not colony
            shipReady: true,
        );

        try {
            $this->bus->dispatch(new ColonizePlanetCommand($ship->getId(), $target->getId()));
        } catch (NotAColonyShipException) {
            // expected
        }

        $this->em->clear();

        $reloadedHome = $this->em->find(Planet::class, $home->getId());
        self::assertSame(100, $reloadedHome->getPopulation()->getTotal());
        self::assertSame(50, $reloadedHome->getPopulation()->getAssigned());

        $reloadedTarget = $this->em->find(Planet::class, $target->getId());
        self::assertNull($reloadedTarget->getPlayer());

        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertNotNull($repo->find($ship->getId()));
    }

    /**
     * @return array{Player, Planet, Planet, Ship}
     */
    private function seed(
        int $popHomeTotal,
        int $popHomeAssigned,
        ShipType $shipType,
        bool $shipReady,
    ): array {
        $player = new Player(PlayerId::generate());
        $home = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($home);

        $home->getPopulation()->grow($popHomeTotal);
        if ($popHomeAssigned > 0) {
            $home->getPopulation()->assign($popHomeAssigned);
        }

        // Target ist ein un-claimed Planet, in keinem Player
        $target = Planet::generatePlanet(PlanetId::generate());

        $ship = new Ship(
            id: ShipId::generate(),
            type: $shipType,
            populationAssigned: $popHomeAssigned,
        );
        $ship->setPlanet($home);
        $ship->setFinishedAt(
            $shipReady
                ? new DateTimeImmutable('-1 hour')
                : new DateTimeImmutable('+1 hour'),
        );

        $this->em->persist($player);
        $this->em->persist($target);
        $this->em->persist($ship);
        $this->em->flush();

        return [$player, $home, $target, $ship];
    }
}
