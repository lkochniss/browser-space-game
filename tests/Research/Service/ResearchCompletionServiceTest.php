<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\AdjustableClock;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\PlayerResearch;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use App\Research\Service\ResearchCompletionService;
use App\Research\Service\StartResearchCommandService;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class ResearchCompletionServiceTest extends IntegrationTestCase
{
    public function test_no_active_research_returns_zero(): void
    {
        $player = $this->seedPlayer();
        $service = self::getContainer()->get(ResearchCompletionService::class);

        self::assertSame(0, $service->runTickForPlayer($player));
    }

    public function test_running_research_not_yet_finished(): void
    {
        $player = $this->seedPlayer();
        self::getContainer()->get(StartResearchCommandService::class)
            ->__invoke($player->getId(), 'basic_mining');

        $service = self::getContainer()->get(ResearchCompletionService::class);
        // Clock nicht advanced → finished_at noch in Zukunft
        self::assertSame(0, $service->runTickForPlayer($player));
    }

    public function test_finished_research_creates_player_research(): void
    {
        $player = $this->seedPlayer();
        self::getContainer()->get(StartResearchCommandService::class)
            ->__invoke($player->getId(), 'basic_mining');

        // basic_mining baseDuration = 240s
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(241);

        $service = self::getContainer()->get(ResearchCompletionService::class);
        self::assertSame(1, $service->runTickForPlayer($player));

        /** @var PlayerResearchRepository $repo */
        $repo = self::getContainer()->get(PlayerResearchRepository::class);
        $entry = $repo->findOneByPlayerAndSlug($player, 'basic_mining');
        self::assertNotNull($entry);
        self::assertSame(1, $entry->getLevel());

        /** @var ActiveResearchRepository $arRepo */
        $arRepo = self::getContainer()->get(ActiveResearchRepository::class);
        self::assertNull($arRepo->findActiveForPlayer($player));
    }

    public function test_multi_level_progression(): void
    {
        // construction_speed_1 hat maxLevel=3, prereqs: metallurgy L1 + IRON_SMELTER L1.
        // Seed beide vor dem Test damit wir Level-Progression testen können.
        $player = $this->seedPlayer(withSmelter: true, preSeedMetallurgy: true);
        $start = self::getContainer()->get(StartResearchCommandService::class);
        $clock = self::getContainer()->get(AdjustableClock::class);
        $completion = self::getContainer()->get(ResearchCompletionService::class);

        // L1: 600s baseDuration
        $start->__invoke($player->getId(), 'construction_speed_1');
        $clock->advanceSeconds(601);
        self::assertSame(1, $completion->runTickForPlayer($player));

        // L2: 600 × 2 = 1200s
        $start->__invoke($player->getId(), 'construction_speed_1');
        $clock->advanceSeconds(1201);
        self::assertSame(1, $completion->runTickForPlayer($player));

        /** @var PlayerResearchRepository $repo */
        $repo = self::getContainer()->get(PlayerResearchRepository::class);
        $entry = $repo->findOneByPlayerAndSlug($player, 'construction_speed_1');
        self::assertSame(2, $entry->getLevel());
    }

    private function seedPlayer(bool $withSmelter = false, bool $preSeedMetallurgy = false): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 50000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 50000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COPPER_ORE, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::SILICON, 5000));

        $now = new DateTimeImmutable('-1 minute');
        $lab = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
        $lab->setFinishedAt($now);
        $planet->addBuilding($lab);

        // basic_mining-Prereq: IRON_MINE L1
        $mine = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $mine->setFinishedAt($now);
        $planet->addBuilding($mine);

        if ($withSmelter) {
            $smelter = new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 1);
            $smelter->setFinishedAt($now);
            $planet->addBuilding($smelter);
        }

        $this->em->persist($player);
        $this->em->flush();

        if ($preSeedMetallurgy) {
            $entry = PlayerResearch::generate($player, 'metallurgy', 1);
            $this->em->persist($entry);
            $this->em->flush();
        }

        return $player;
    }
}
