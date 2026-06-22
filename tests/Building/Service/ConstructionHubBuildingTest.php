<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Faction\Service\FactionSeedService;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class ConstructionHubBuildingTest extends IntegrationTestCase
{
    public function test_construction_hub_is_unique(): void
    {
        self::assertTrue(BuildingType::CONSTRUCTION_HUB->isUnique());
    }

    public function test_construction_hub_slot_size_is_2(): void
    {
        self::assertSame(2, BuildingType::CONSTRUCTION_HUB->getSlotSize());
    }

    public function test_no_hub_returns_1x_multiplier(): void
    {
        $planet = $this->bootstrapPlanet();
        self::assertSame(1.0, $planet->getConstructionHubSpeedMultiplier(new DateTimeImmutable()));
    }

    public function test_unfinished_hub_no_boost(): void
    {
        $planet = $this->bootstrapPlanet();
        $hub = new Building(BuildingId::generate(), BuildingType::CONSTRUCTION_HUB, 1);
        $hub->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($hub);

        self::assertSame(1.0, $planet->getConstructionHubSpeedMultiplier(new DateTimeImmutable()));
    }

    public function test_finished_hub_l1_gives_110_multiplier(): void
    {
        $planet = $this->bootstrapPlanet();
        $hub = new Building(BuildingId::generate(), BuildingType::CONSTRUCTION_HUB, 1);
        $hub->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($hub);

        self::assertEqualsWithDelta(1.10, $planet->getConstructionHubSpeedMultiplier(new DateTimeImmutable()), 0.001);
    }

    public function test_finished_hub_l3_gives_1331_multiplier(): void
    {
        $planet = $this->bootstrapPlanet();
        $hub = new Building(BuildingId::generate(), BuildingType::CONSTRUCTION_HUB, 3);
        $hub->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($hub);

        self::assertEqualsWithDelta(1.331, $planet->getConstructionHubSpeedMultiplier(new DateTimeImmutable()), 0.001);
    }

    public function test_build_with_construction_hub_is_faster(): void
    {
        $bus = self::getContainer()->get(CommandBusInterface::class);
        $planet = $this->bootstrapPlanet();

        // Baseline: IRON_MINE-Build ohne Hub
        $b1 = $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $duration1 = $b1->getFinishedAt()->getTimestamp()
            - (new DateTimeImmutable())->getTimestamp();

        // Construction-Hub L1 hinzufügen → 10% schneller
        $hub = new Building(BuildingId::generate(), BuildingType::CONSTRUCTION_HUB, 1);
        $hub->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($hub);
        $this->em->flush();

        $b2 = $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $duration2 = $b2->getFinishedAt()->getTimestamp()
            - (new DateTimeImmutable())->getTimestamp();

        self::assertLessThan($duration1, $duration2, 'mit Construction-Hub muss kürzer sein');
    }

    private function bootstrapPlanet(): \App\Planet\Model\Planet
    {
        $bus = self::getContainer()->get(CommandBusInterface::class);
        self::getContainer()->get(FactionSeedService::class)->seed();
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();
        $bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));
        $planet = self::getContainer()->get(PlanetRepository::class)->find($planetId);

        try {
            $planet->getResource(ResourceType::IRON_ORE)->setAmount(50000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 50000));
        }
        $planet->getPopulation()->grow(500);
        $this->em->flush();

        return $planet;
    }
}
