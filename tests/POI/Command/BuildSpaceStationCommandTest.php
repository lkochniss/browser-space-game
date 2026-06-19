<?php

declare(strict_types=1);

namespace App\Tests\POI\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\POI\Command\BuildSpaceStationCommand;
use App\POI\Exception\InsufficientPopulationException;
use App\POI\Exception\InsufficientResourcesException;
use App\POI\Exception\MissingShipyardInSystemException;
use App\POI\Exception\PlayerNotFoundException;
use App\POI\Exception\SolarSystemNotFoundException;
use App\POI\Exception\StationAlreadyExistsInSystemException;
use App\POI\Model\SpaceStation;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\StationStatus;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class BuildSpaceStationCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_build_succeeds_with_shipyard_l3_resources_pop(): void
    {
        [$player, $system, $planet] = $this->seedPlayerWithShipyard(level: 3);

        $station = $this->bus->dispatch(new BuildSpaceStationCommand(
            $player->getId(),
            $system->getId(),
        ));

        self::assertInstanceOf(SpaceStation::class, $station);
        self::assertSame(StationStatus::ACTIVE, $station->getStatus());
        self::assertSame($player->getId()->__toString(), $station->getOwner()->getId()->__toString());
        self::assertSame(200, $station->getPopulationOnStation());
        self::assertSame(SpaceStation::DEFAULT_STORAGE_CAPACITY, $station->getStorageCapacity());

        $this->em->clear();

        $reloaded = $this->em->find(Planet::class, $planet->getId());
        // 5000 Iron-Bar consumed
        self::assertSame(5000, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        // 1000 aluminum, 200 titanium consumed
        self::assertSame(1000, $reloaded->getResource(ResourceType::ALUMINUM_ORE)->getAmount());
        self::assertSame(50, $reloaded->getResource(ResourceType::TITANIUM_ORE)->getAmount());
        // 200 Pop consumed (start 500 → 300)
        self::assertSame(300, $reloaded->getPopulation()->getTotal());
    }

    public function test_throws_when_player_not_found(): void
    {
        $this->expectException(PlayerNotFoundException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand(
            PlayerId::generate(),
            SolarSystemId::generate(),
        ));
    }

    public function test_throws_when_system_not_found(): void
    {
        [$player] = $this->seedPlayerWithShipyard(level: 3);

        $this->expectException(SolarSystemNotFoundException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand(
            $player->getId(),
            SolarSystemId::generate(),
        ));
    }

    public function test_throws_when_player_lacks_shipyard_in_system(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 0);

        $this->expectException(MissingShipyardInSystemException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand(
            $player->getId(),
            $system->getId(),
        ));
    }

    public function test_throws_when_shipyard_below_required_level(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 2);

        $this->expectException(MissingShipyardInSystemException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand(
            $player->getId(),
            $system->getId(),
        ));
    }

    public function test_throws_when_system_already_has_station(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 3);

        $this->bus->dispatch(new BuildSpaceStationCommand($player->getId(), $system->getId()));

        $this->expectException(StationAlreadyExistsInSystemException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand($player->getId(), $system->getId()));
    }

    public function test_throws_when_resources_insufficient(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 3, ironBar: 100);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand($player->getId(), $system->getId()));
    }

    public function test_throws_when_pop_insufficient(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 3, popTotal: 50);

        $this->expectException(InsufficientPopulationException::class);
        $this->bus->dispatch(new BuildSpaceStationCommand($player->getId(), $system->getId()));
    }

    public function test_built_station_appears_in_system_pois(): void
    {
        [$player, $system] = $this->seedPlayerWithShipyard(level: 3);

        $this->bus->dispatch(new BuildSpaceStationCommand($player->getId(), $system->getId()));

        $repo = self::getContainer()->get(PoiRepository::class);
        $pois = $repo->findBySolarSystem($system);
        $stations = array_filter($pois, fn ($p) => $p instanceof SpaceStation);

        self::assertCount(1, $stations);
    }

    /**
     * @return array{Player, SolarSystem, Planet}
     */
    private function seedPlayerWithShipyard(
        int $level,
        int $ironBar = 10000,
        int $aluminumOre = 2000,
        int $titaniumOre = 250,
        int $popTotal = 500,
    ): array {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $system->addPlanet($planet);

        if ($level > 0) {
            $planet->addBuilding(new Building(
                BuildingId::generate(),
                BuildingType::SHIPYARD,
                $level,
            ));
        }

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $ironBar));
        $planet->addResource(Resource::generateWithAmount(ResourceType::ALUMINUM_ORE, $aluminumOre));
        $planet->addResource(Resource::generateWithAmount(ResourceType::TITANIUM_ORE, $titaniumOre));

        if ($popTotal > 0) {
            $planet->getPopulation()->setCap(max(100, $popTotal + 100));
            $planet->getPopulation()->grow($popTotal);
        }

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->flush();

        return [$player, $system, $planet];
    }
}
