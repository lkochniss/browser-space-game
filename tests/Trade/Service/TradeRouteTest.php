<?php

declare(strict_types=1);

namespace App\Tests\Trade\Service;

use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\Fleet\Service\FleetArrivalService;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use App\Trade\Command\CancelRouteCommand;
use App\Trade\Command\CreateFixedRouteCommand;
use App\Trade\Command\CreateSingleTripCommand;
use App\Trade\Command\PauseRouteCommand;
use App\Trade\Command\ResumeRouteCommand;
use App\Trade\Exception\InvalidTradeRouteException;
use App\Trade\Exception\ShipAlreadyBoundException;
use App\Trade\Model\TradeRoute;
use App\Trade\Repository\TradeRouteRepository;
use App\Trade\Service\TradeRouteProcessor;
use App\Trade\ValueObject\TradeRouteLeg;
use App\Trade\ValueObject\TradeRouteStatus;
use DateTimeImmutable;

final class TradeRouteTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;
    private TradeRouteProcessor $processor;
    private AdjustableClock $clock;
    private FleetArrivalService $arrival;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
        $this->processor = self::getContainer()->get(TradeRouteProcessor::class);
        $this->clock = self::getContainer()->get(AdjustableClock::class);
        $this->arrival = self::getContainer()->get(FleetArrivalService::class);
    }

    public function test_create_fixed_route_binds_ship(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();

        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));

        self::assertInstanceOf(TradeRoute::class, $route);
        self::assertSame(TradeRouteStatus::ACTIVE, $route->getStatus());
        self::assertSame(TradeRouteLeg::AT_SOURCE, $route->getCurrentLeg());
        self::assertNotNull($ship->getFleet());
    }

    public function test_create_single_trip_marks_status(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();

        $route = $this->bus->dispatch(new CreateSingleTripCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            resource: ResourceType::IRON_ORE,
            qty: 50,
        ));

        self::assertSame(TradeRouteStatus::SINGLE_TRIP, $route->getStatus());
    }

    public function test_ship_already_bound_throws(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();

        $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));

        $this->expectException(ShipAlreadyBoundException::class);
        $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));
    }

    public function test_same_source_target_throws(): void
    {
        [$player, $source, , $ship] = $this->seedTwoPlanetSetup();

        $this->expectException(InvalidTradeRouteException::class);
        $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $source->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));
    }

    public function test_processor_advances_at_source_to_going_to_target(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));

        $this->processor->runTick();
        $this->em->refresh($route);
        $this->em->refresh($ship);

        self::assertSame(TradeRouteLeg::GOING_TO_TARGET, $route->getCurrentLeg());
        self::assertNull($ship->getPlanet(), 'Ship in Transit hat keinen Planet');
        // Source-Resource verringert um 50
        $this->em->refresh($source);
        self::assertSame(950, $source->getResource(ResourceType::IRON_ORE)->getAmount());
        // Cargo geladen
        self::assertSame(50, $ship->getCargo()->getResource(ResourceType::IRON_ORE));
    }

    public function test_processor_completes_single_trip_after_arrival(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $route = $this->bus->dispatch(new CreateSingleTripCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            resource: ResourceType::IRON_ORE,
            qty: 50,
        ));

        // Tick 1 — dispatched
        $this->processor->runTick();
        // Clock voraus → arrival
        $this->clock->advanceSeconds(100_000);
        $this->arrival->resolveArrivedFleets();

        // Tick 2 — AT_TARGET → unload + Single-Trip cancels
        $this->processor->runTick();
        $this->em->refresh($route);
        $this->em->refresh($target);

        self::assertSame(TradeRouteStatus::CANCELLED, $route->getStatus());
        self::assertSame(50, $target->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(1, $route->getTripCounter());
    }

    public function test_fixed_route_loops_back_to_source(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        // Add Return-Resource at Target so Return-Leg can load.
        $target->addResource(Resource::generateWithAmount(ResourceType::COAL, 500));
        $this->em->flush();

        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
            returnResource: ResourceType::COAL,
            returnQty: 30,
        ));

        // Outbound trip
        $this->processor->runTick();
        $this->clock->advanceSeconds(100_000);
        $this->arrival->resolveArrivedFleets();
        $this->processor->runTick(); // AT_TARGET → unload + load return + move
        $this->em->refresh($route);
        self::assertSame(TradeRouteLeg::GOING_TO_SOURCE, $route->getCurrentLeg());

        // Return trip arrival — Loop läuft direkt in nächste Outbound-Leg
        $this->clock->advanceSeconds(100_000);
        $this->arrival->resolveArrivedFleets();
        $this->processor->runTick();
        $this->em->refresh($route);
        $this->em->refresh($source);
        $this->em->refresh($target);

        // Nach Return-Arrival: AT_SOURCE-Logic feuert direkt nächste Outbound →
        // GOING_TO_TARGET. Fixed-Route bleibt ACTIVE.
        self::assertSame(TradeRouteLeg::GOING_TO_TARGET, $route->getCurrentLeg());
        self::assertSame(TradeRouteStatus::ACTIVE, $route->getStatus(), 'Fixed-Route bleibt active');
        // Source bekam 30 COAL (return)
        self::assertSame(30, $source->getResource(ResourceType::COAL)->getAmount());
        // Source IRON_ORE = 1000 - 50 (Outbound-1) - 50 (Outbound-2 nach Reload) = 900
        self::assertSame(900, $source->getResource(ResourceType::IRON_ORE)->getAmount());
        // Target hat 50 IRON_ORE (Outbound-1 delivered)
        self::assertSame(50, $target->getResource(ResourceType::IRON_ORE)->getAmount());
        // 2 Leg-Completions registered (Outbound + Return)
        self::assertSame(2, $route->getTripCounter());
    }

    public function test_pause_stops_processor(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));
        $this->bus->dispatch(new PauseRouteCommand($route->getId()));

        $this->processor->runTick();
        $this->em->refresh($route);

        self::assertSame(TradeRouteStatus::PAUSED, $route->getStatus());
        self::assertSame(TradeRouteLeg::AT_SOURCE, $route->getCurrentLeg(), 'Leg bleibt unverändert');
    }

    public function test_resume_after_pause(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));
        $this->bus->dispatch(new PauseRouteCommand($route->getId()));
        $this->bus->dispatch(new ResumeRouteCommand($route->getId()));

        $this->em->refresh($route);
        self::assertSame(TradeRouteStatus::ACTIVE, $route->getStatus());
    }

    public function test_cancel_releases_ship(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));
        $fleet = $ship->getFleet();

        $this->bus->dispatch(new CancelRouteCommand($route->getId()));

        $this->em->refresh($ship);
        $this->em->refresh($route);
        self::assertSame(TradeRouteStatus::CANCELLED, $route->getStatus());
        self::assertNull($ship->getFleet());
        // Solo-Fleet wurde entfernt
        $reloadedFleet = $this->em->find(\App\Fleet\Model\Fleet::class, $fleet->getId());
        self::assertNull($reloadedFleet);
    }

    public function test_empty_source_stops_gracefully(): void
    {
        [$player, $source, $target, $ship] = $this->seedTwoPlanetSetup();
        $source->getResource(ResourceType::IRON_ORE)->setAmount(0);
        $this->em->flush();

        $route = $this->bus->dispatch(new CreateFixedRouteCommand(
            playerId: $player->getId(),
            shipId: $ship->getId(),
            sourcePlanetId: $source->getId(),
            targetPlanetId: $target->getId(),
            outboundResource: ResourceType::IRON_ORE,
            outboundQty: 50,
        ));

        $this->processor->runTick();
        $this->em->refresh($route);

        // Bleibt in AT_SOURCE, kein State-Change weil leer
        self::assertSame(TradeRouteLeg::AT_SOURCE, $route->getCurrentLeg());
        self::assertSame(TradeRouteStatus::ACTIVE, $route->getStatus());
    }

    /**
     * @return array{Player, Planet, Planet, Ship}
     */
    private function seedTwoPlanetSetup(): array
    {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'TestSys');

        $source = Planet::generatePlanet(PlanetId::generate());
        $source->setSolarSystem($system);
        $player->claimPlanet($source);

        $target = Planet::generatePlanet(PlanetId::generate());
        $target->setSolarSystem($system);
        $player->claimPlanet($target);

        $source->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 1000));

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::TRANSPORT_MEDIUM,
            populationAssigned: 25,
            cargoVolumeCapacity: 500,
        );
        $ship->setPlanet($source);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($ship);
        $this->em->flush();

        return [$player, $source, $target, $ship];
    }
}
