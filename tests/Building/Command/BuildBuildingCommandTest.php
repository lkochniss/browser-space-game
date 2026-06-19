<?php

declare(strict_types=1);

namespace App\Tests\Building\Command;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Exception\InsufficientPopulationException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class BuildBuildingCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_build_iron_mine_succeeds_with_sufficient_resources_and_pop(): void
    {
        $planet = $this->seedPlanet(iron: 200, popTotal: 50);
        $planetId = $planet->getId();

        $building = $this->bus->dispatch(new BuildBuildingCommand($planetId, BuildingType::IRON_MINE));

        self::assertSame(BuildingType::IRON_MINE, $building->getType());
        self::assertSame(1, $building->getLevel());
        // T-062: finishedAt set to now + duration → in future relative to build time
        self::assertNotNull($building->getFinishedAt());
        self::assertGreaterThan(new \DateTimeImmutable('-1 second'), $building->getFinishedAt());

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        self::assertSame(150, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(5, $reloaded->getPopulation()->getAssigned());
        self::assertSame(45, $reloaded->getPopulation()->getFree());
        self::assertCount(1, $reloaded->getBuildings());
    }

    public function test_build_hub_in_progress_does_not_raise_cap(): void
    {
        // T-062: Hub-Bau hat Bauzeit. Während Bauphase kein Cap-Bonus.
        $planet = $this->seedPlanet(iron: 200, coal: 100, popTotal: 80);
        $planetId = $planet->getId();

        $this->bus->dispatch(new BuildBuildingCommand($planetId, BuildingType::HUB));

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        // Cap stays at base 100 — Hub is still being constructed
        self::assertSame(100, $reloaded->getPopulation()->getCap());
        self::assertSame(100, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(50, $reloaded->getResource(ResourceType::COAL)->getAmount());
        self::assertSame(10, $reloaded->getPopulation()->getAssigned());
        // finishedAt is in the future
        $hub = $reloaded->getBuildings()->first();
        self::assertGreaterThan(new \DateTimeImmutable(), $hub->getFinishedAt());
    }

    public function test_throws_when_resource_amount_insufficient(): void
    {
        $planet = $this->seedPlanet(iron: 30, popTotal: 50);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
    }

    public function test_throws_when_resource_not_present_on_planet(): void
    {
        // HUB needs Coal — planet has only iron, no coal resource entry
        $planet = $this->seedPlanet(iron: 200, popTotal: 50);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
    }

    public function test_throws_when_free_population_insufficient(): void
    {
        $planet = $this->seedPlanet(iron: 200, popTotal: 3);

        $this->expectException(InsufficientPopulationException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
    }

    public function test_throws_when_planet_not_found(): void
    {
        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new BuildBuildingCommand(PlanetId::generate(), BuildingType::IRON_MINE));
    }

    public function test_no_state_change_when_validation_fails(): void
    {
        $planet = $this->seedPlanet(iron: 30, popTotal: 50);
        $planetId = $planet->getId();

        try {
            $this->bus->dispatch(new BuildBuildingCommand($planetId, BuildingType::IRON_MINE));
        } catch (InsufficientResourcesException) {
            // expected
        }

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        self::assertSame(30, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $reloaded->getPopulation()->getAssigned());
        self::assertCount(0, $reloaded->getBuildings());
    }

    private function seedPlanet(int $iron = 0, int $coal = 0, int $popTotal = 0): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $iron));
        if ($coal > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, $coal));
        }

        if ($popTotal > 0) {
            $planet->getPopulation()->grow($popTotal);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }
}
