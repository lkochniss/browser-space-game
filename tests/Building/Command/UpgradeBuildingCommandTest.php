<?php

declare(strict_types=1);

namespace App\Tests\Building\Command;

use App\Building\Command\UpgradeBuildingCommand;
use App\Building\Exception\BuildingNotFoundException;
use App\Building\Exception\InsufficientPopulationException;
use App\Building\Exception\InsufficientResourcesException;
use App\Building\Exception\PlanetNotFoundException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class UpgradeBuildingCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_upgrade_iron_mine_l1_to_l2_doubles_cost(): void
    {
        // L1 → L2 needs 100 Iron + 10 Pop (base 50/5 * 2^1)
        [$planet, $building] = $this->seedPlanetWithBuilding(
            type: BuildingType::IRON_MINE,
            iron: 200,
            popTotal: 80,
            assignedFromBuild: 5,
        );

        $upgraded = $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));

        self::assertSame(2, $upgraded->getLevel());

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());
        self::assertSame(100, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount()); // 200 - 100
        self::assertSame(15, $reloaded->getPopulation()->getAssigned()); // 5 (build) + 10 (upgrade)
    }

    public function test_upgrade_hub_drops_cap_during_construction(): void
    {
        // T-062: Während Upgrade-Phase ist Hub "not ready" → kein Cap-Bonus.
        // Cap fällt von L1-Wert (150) auf base (100), bis Upgrade fertig.
        [$planet, $building] = $this->seedPlanetWithBuilding(
            type: BuildingType::HUB,
            iron: 500,
            coal: 200,
            popTotal: 100,
            assignedFromBuild: 10,
        );

        // Pre-upgrade: Hub L1 finishedAt = null → ready → cap = 150
        self::assertSame(150, $planet->getPopulation()->getCap());

        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());

        // Hub Level wurde inkrementiert, aber finishedAt liegt in Zukunft → not ready → cap = base 100
        self::assertSame(100, $reloaded->getPopulation()->getCap());
        self::assertSame(2, $reloaded->getBuildings()->first()->getLevel());
        self::assertGreaterThan(new \DateTimeImmutable(), $reloaded->getBuildings()->first()->getFinishedAt());
        self::assertSame(300, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount()); // 500 - 200
        self::assertSame(100, $reloaded->getResource(ResourceType::COAL)->getAmount()); // 200 - 100
        self::assertSame(30, $reloaded->getPopulation()->getAssigned()); // 10 + 20
    }

    public function test_throws_when_resources_insufficient_for_upgrade(): void
    {
        [$planet, $building] = $this->seedPlanetWithBuilding(
            type: BuildingType::IRON_MINE,
            iron: 50, // not enough for L1 → L2 (needs 100)
            popTotal: 50,
            assignedFromBuild: 5,
        );

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));
    }

    public function test_throws_when_pop_insufficient_for_upgrade(): void
    {
        [$planet, $building] = $this->seedPlanetWithBuilding(
            type: BuildingType::IRON_MINE,
            iron: 200,
            popTotal: 9, // 5 already assigned, 4 free, need 10 for L1→L2
            assignedFromBuild: 5,
        );

        $this->expectException(InsufficientPopulationException::class);
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));
    }

    public function test_throws_when_building_not_found(): void
    {
        [$planet] = $this->seedPlanetWithBuilding(
            type: BuildingType::IRON_MINE,
            iron: 200,
            popTotal: 50,
            assignedFromBuild: 5,
        );

        $this->expectException(BuildingNotFoundException::class);
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), BuildingId::generate()));
    }

    public function test_throws_when_planet_not_found(): void
    {
        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new UpgradeBuildingCommand(PlanetId::generate(), BuildingId::generate()));
    }

    public function test_no_state_change_on_validation_failure(): void
    {
        [$planet, $building] = $this->seedPlanetWithBuilding(
            type: BuildingType::IRON_MINE,
            iron: 50,
            popTotal: 50,
            assignedFromBuild: 5,
        );
        $planetId = $planet->getId();

        try {
            $this->bus->dispatch(new UpgradeBuildingCommand($planetId, $building->getId()));
        } catch (InsufficientResourcesException) {
            // expected
        }

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planetId);

        self::assertSame(50, $reloaded->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(5, $reloaded->getPopulation()->getAssigned());
        self::assertSame(1, $reloaded->getBuildings()->first()->getLevel());
    }

    /**
     * Seeds a player+planet with a single Building already at level 1 (simulating a completed Build).
     * Resources/Pop reflect post-build state (caller passes assignedFromBuild = initial-build's pop-cost).
     *
     * @return array{Planet, Building}
     */
    private function seedPlanetWithBuilding(
        BuildingType $type,
        int $iron = 0,
        int $coal = 0,
        int $popTotal = 0,
        int $assignedFromBuild = 0,
    ): array {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        if ($iron > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $iron));
        }
        if ($coal > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, $coal));
        }
        if ($popTotal > 0) {
            $planet->getPopulation()->grow($popTotal);
        }

        $building = Building::createNewBuilding($type);
        $planet->addBuilding($building);
        if ($assignedFromBuild > 0) {
            $planet->getPopulation()->assign($assignedFromBuild);
        }

        $this->em->persist($player);
        $this->em->flush();

        return [$planet, $building];
    }
}
