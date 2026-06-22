<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Research\Model\PlayerResearch;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Exception\InsufficientPopulationException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\MissingShipyardException;
use App\Ship\Exception\PlanetNotFoundException;
use App\Ship\Exception\PropulsionResearchNotMetException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\PropulsionType;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;

final class BuildShipCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_build_ship_succeeds_with_shipyard_resources_and_pop(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50, shipyardLevel: 1);
        $planetId = $planet->getId();

        $ship = $this->bus->dispatch(new BuildShipCommand($planetId));

        self::assertInstanceOf(Ship::class, $ship);
        self::assertSame(ShipType::GENERIC, $ship->getType());
        self::assertSame(20, $ship->getPopulationAssigned());
        self::assertSame($planet->getId(), $ship->getPlanet()?->getId());
        self::assertNotNull($ship->getFinishedAt());
        self::assertGreaterThan(new \DateTimeImmutable(), $ship->getFinishedAt());

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        self::assertSame(100, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(20, $reloaded->getPopulation()->getAssigned());

        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertCount(1, $repo->findByPlanet($reloaded));
    }

    public function test_throws_when_planet_not_found(): void
    {
        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new BuildShipCommand(PlanetId::generate()));
    }

    public function test_throws_when_no_shipyard(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50, shipyardLevel: 0);

        $this->expectException(MissingShipyardException::class);
        $this->bus->dispatch(new BuildShipCommand($planet->getId()));
    }

    public function test_throws_when_iron_bar_insufficient(): void
    {
        $planet = $this->seedPlanet(ironBar: 50, popTotal: 50, shipyardLevel: 1);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildShipCommand($planet->getId()));
    }

    public function test_throws_when_iron_bar_resource_missing(): void
    {
        $planet = $this->seedPlanetWithoutIronBar(popTotal: 50, shipyardLevel: 1);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildShipCommand($planet->getId()));
    }

    public function test_throws_when_pop_insufficient(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 10, shipyardLevel: 1);

        $this->expectException(InsufficientPopulationException::class);
        $this->bus->dispatch(new BuildShipCommand($planet->getId()));
    }

    public function test_build_colony_ship_uses_higher_cost_and_pop(): void
    {
        $planet = $this->seedPlanet(ironBar: 500, popTotal: 100, shipyardLevel: 1);
        $planetId = $planet->getId();

        $ship = $this->bus->dispatch(new BuildShipCommand($planetId, ShipType::COLONY_SHIP));

        self::assertSame(ShipType::COLONY_SHIP, $ship->getType());
        self::assertSame(50, $ship->getPopulationAssigned());

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        // 500 - 300 = 200, pop assigned = 50
        self::assertSame(200, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(50, $reloaded->getPopulation()->getAssigned());
    }

    public function test_no_state_change_when_validation_fails(): void
    {
        $planet = $this->seedPlanet(ironBar: 50, popTotal: 50, shipyardLevel: 1);
        $planetId = $planet->getId();

        try {
            $this->bus->dispatch(new BuildShipCommand($planetId));
        } catch (InsufficientResourcesException) {
            // expected
        }

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        self::assertSame(50, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(0, $reloaded->getPopulation()->getAssigned());

        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertCount(0, $repo->findByPlanet($reloaded));
    }

    public function test_ship_defaults_to_hydrogen_propulsion(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50, shipyardLevel: 1);
        $ship = $this->bus->dispatch(new BuildShipCommand($planet->getId()));

        self::assertSame(PropulsionType::HYDROGEN, $ship->getPropulsion());
    }

    public function test_propulsion_ion_requires_research(): void
    {
        // T-026c: Player ohne propulsion_ion-Research kann kein ION-Schiff bauen
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50, shipyardLevel: 1);

        $this->expectException(PropulsionResearchNotMetException::class);
        $this->bus->dispatch(new BuildShipCommand(
            $planet->getId(),
            ShipType::GENERIC,
            PropulsionType::ION,
        ));
    }

    public function test_propulsion_ion_builds_when_research_granted(): void
    {
        $planet = $this->seedPlanet(ironBar: 200, popTotal: 50, shipyardLevel: 1);
        $player = $planet->getPlayer();
        $this->em->persist(PlayerResearch::generate($player, 'propulsion_ion', 1));
        $this->em->flush();

        $ship = $this->bus->dispatch(new BuildShipCommand(
            $planet->getId(),
            ShipType::GENERIC,
            PropulsionType::ION,
        ));

        self::assertSame(PropulsionType::ION, $ship->getPropulsion());
        // Effective Speed = ShipType.GENERIC (1.0) × ION (1.3) = 1.3
        self::assertEqualsWithDelta(1.3, $ship->getEffectiveSpeed(), 0.0001);
    }

    private function seedPlanet(int $ironBar, int $popTotal, int $shipyardLevel): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $ironBar));

        if ($shipyardLevel > 0) {
            $planet->addBuilding(new Building(
                \App\Building\ValueObject\BuildingId::generate(),
                BuildingType::SHIPYARD,
                $shipyardLevel,
            ));
        }

        if ($popTotal > 0) {
            $planet->getPopulation()->grow($popTotal);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }

    private function seedPlanetWithoutIronBar(int $popTotal, int $shipyardLevel): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        if ($shipyardLevel > 0) {
            $planet->addBuilding(new Building(
                \App\Building\ValueObject\BuildingId::generate(),
                BuildingType::SHIPYARD,
                $shipyardLevel,
            ));
        }

        if ($popTotal > 0) {
            $planet->getPopulation()->grow($popTotal);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }
}
