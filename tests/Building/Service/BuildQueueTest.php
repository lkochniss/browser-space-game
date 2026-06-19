<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Command\UpgradeBuildingCommand;
use App\Building\Exception\BuildQueueFullException;
use App\Building\Service\BuildBuildingCommandService;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\Faction\Service\FactionSeedService;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class BuildQueueTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_max_3_parallel_builds(): void
    {
        $planet = $this->bootstrapPlanet();

        // 3 verschiedene Tier-0 Buildings parallel — alle drei sollten durchlaufen
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::WATER_TANK));

        $clock = self::getContainer()->get(AdjustableClock::class);
        self::assertSame(3, $planet->countActiveBuildJobs($clock->now()));
    }

    public function test_4th_build_throws_queue_full(): void
    {
        $planet = $this->bootstrapPlanet();

        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::WATER_TANK));

        $this->expectException(BuildQueueFullException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::FOOD_SILO));
    }

    public function test_upgrade_counts_against_queue_slots(): void
    {
        $planet = $this->bootstrapPlanet();

        // 1× IRON_MINE bauen, dann fertig stellen
        $building = $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(99999); // ready
        $this->em->refresh($building);
        // 2 weitere parallel starten
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::WATER_TANK));
        // Upgrade auf existing IRON_MINE = 3. Slot
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));

        // 4. Build sollte werfen
        $this->expectException(BuildQueueFullException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::FOOD_SILO));
    }

    public function test_finished_build_frees_slot(): void
    {
        $planet = $this->bootstrapPlanet();

        $b1 = $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::WATER_TANK));

        // Clock voraus → b1 fertig (b2/b3 dauern länger, sind noch active)
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(400); // > IRON_MINE-Dauer (300s), < HUB (1800s)

        $active = $planet->countActiveBuildJobs($clock->now());
        self::assertSame(2, $active, 'IRON_MINE fertig, 2 weitere noch aktiv');

        // 3. Slot frei → neuer Bau geht
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::FOOD_SILO));

        self::assertSame(3, $planet->countActiveBuildJobs($clock->now()));
    }

    private function bootstrapPlanet(): \App\Planet\Model\Planet
    {
        self::getContainer()->get(FactionSeedService::class)->seed();
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));
        $planet = self::getContainer()->get(PlanetRepository::class)->find($planetId);

        // Resources + Pop großzügig
        foreach ([
            ResourceType::IRON_ORE, ResourceType::COAL,
        ] as $r) {
            try {
                $planet->getResource($r)->setAmount(10000);
            } catch (\Throwable) {
                $planet->addResource(Resource::generateWithAmount($r, 10000));
            }
        }
        $planet->getPopulation()->grow(500);
        $this->em->flush();

        return $planet;
    }
}
