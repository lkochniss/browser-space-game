<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Exception\BuildingAlreadyExistsException;
use App\Building\Exception\PlanetSlotsFullException;
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

final class BuildingUniquenessTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_unique_building_second_build_throws(): void
    {
        // T-172: HQ wird bei ClaimStartPlanet auto-built → 2. Build muss werfen.
        $planet = $this->bootstrapPlanet();

        $this->expectException(BuildingAlreadyExistsException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HQ));
    }

    public function test_non_unique_building_can_be_built_multiple_times(): void
    {
        $planet = $this->bootstrapPlanet();
        // ClaimStartPlanet legt schon 1 IRON_MINE an
        $initial = 0;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getType() === BuildingType::IRON_MINE) {
                $initial++;
            }
        }

        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::IRON_MINE));

        $count = 0;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getType() === BuildingType::IRON_MINE) {
                $count++;
            }
        }
        self::assertSame($initial + 2, $count, 'IRON_MINE ist non-unique → mehrere Instanzen erlaubt');
    }

    public function test_slot_cap_blocks_when_full(): void
    {
        // MEDIUM = 18 Slots. ClaimStartPlanet legt HQ (3) + IRON_MINE (1) = 4 belegt.
        // 13 weitere IRON_MINE → 17 used. HUB braucht nur 1 → ok (18). HUB+1 → würfe.
        $planet = $this->bootstrapPlanet();
        for ($i = 0; $i < 13; $i++) {
            $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
            $planet->addBuilding($b);
        }
        $this->em->flush();
        // 17 belegt. 1 frei. RESEARCH_LAB braucht 3 Slots → wirft (Tier-0 size-3 ohne Tech-Lock).

        $this->expectException(PlanetSlotsFullException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::RESEARCH_LAB));
    }

    public function test_size_aware_specialization_via_direct_fill(): void
    {
        // MEDIUM Planet hat 18 Slots. HQ(3) + IRON_MINE(1) = 4 belegt nach ClaimStart.
        // Fülle bis 18, dann 19. via dispatch muss werfen.
        $planet = $this->bootstrapPlanet();
        for ($i = 0; $i < 14; $i++) {
            $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
            $planet->addBuilding($b);
        }
        $this->em->flush();

        self::assertSame(18, $planet->getBuildingSlotsUsed());
        self::assertSame(18, $planet->getBuildingSlotCap());

        $this->expectException(PlanetSlotsFullException::class);
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::FOOD_SILO));
    }

    public function test_unique_strategic_buildings_are_unique(): void
    {
        // T-172: HQ ist unique (HUB nicht mehr).
        foreach ([BuildingType::HQ, BuildingType::RESEARCH_LAB, BuildingType::SHIPYARD,
                  BuildingType::PROBE_LAB, BuildingType::RECYCLING_PLANT, BuildingType::TELESCOPE,
                  BuildingType::CONSTRUCTION_YARD] as $bt) {
            self::assertTrue($bt->isUnique(), $bt->value . ' soll unique sein');
        }
        foreach ([BuildingType::HUB, BuildingType::IRON_MINE, BuildingType::COAL_MINE,
                  BuildingType::WATER_TANK, BuildingType::WATER_RECLAIMER,
                  BuildingType::IRON_SMELTER] as $bt) {
            self::assertFalse($bt->isUnique(), $bt->value . ' soll non-unique sein');
        }
    }

    private function bootstrapPlanet(): \App\Planet\Model\Planet
    {
        self::getContainer()->get(FactionSeedService::class)->seed();
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));
        $planet = self::getContainer()->get(PlanetRepository::class)->find($planetId);

        // Resources & Pop großzügig
        try {
            $planet->getResource(ResourceType::IRON_ORE)->setAmount(50000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 50000));
        }
        try {
            $planet->getResource(ResourceType::COAL)->setAmount(5000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 5000));
        }
        $planet->getPopulation()->grow(500);
        $this->em->flush();

        return $planet;
    }
}
