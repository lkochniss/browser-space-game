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
            ->__invoke($player->getId(), 'mining_efficiency_1');

        $service = self::getContainer()->get(ResearchCompletionService::class);
        // Clock nicht advanced → finished_at noch in Zukunft
        self::assertSame(0, $service->runTickForPlayer($player));
    }

    public function test_finished_research_creates_player_research(): void
    {
        $player = $this->seedPlayer();
        self::getContainer()->get(StartResearchCommandService::class)
            ->__invoke($player->getId(), 'mining_efficiency_1');

        // Clock 5min vorspulen — Lab L1 + L1 = 300s exact
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(301);

        $service = self::getContainer()->get(ResearchCompletionService::class);
        self::assertSame(1, $service->runTickForPlayer($player));

        // PlayerResearch existiert nun mit Level 1
        /** @var PlayerResearchRepository $repo */
        $repo = self::getContainer()->get(PlayerResearchRepository::class);
        $entry = $repo->findOneByPlayerAndSlug($player, 'mining_efficiency_1');
        self::assertNotNull($entry);
        self::assertSame(1, $entry->getLevel());

        // ActiveResearch entfernt
        /** @var ActiveResearchRepository $arRepo */
        $arRepo = self::getContainer()->get(ActiveResearchRepository::class);
        self::assertNull($arRepo->findActiveForPlayer($player));
    }

    public function test_second_research_increments_level(): void
    {
        $player = $this->seedPlayer();
        $start = self::getContainer()->get(StartResearchCommandService::class);
        $clock = self::getContainer()->get(AdjustableClock::class);
        $completion = self::getContainer()->get(ResearchCompletionService::class);

        // L1 abschließen
        $start->__invoke($player->getId(), 'mining_efficiency_1');
        $clock->advanceSeconds(301);
        $completion->runTickForPlayer($player);

        // L2 starten + abschließen (L2 dauert 600s)
        $start->__invoke($player->getId(), 'mining_efficiency_1');
        $clock->advanceSeconds(601);
        self::assertSame(1, $completion->runTickForPlayer($player));

        /** @var PlayerResearchRepository $repo */
        $repo = self::getContainer()->get(PlayerResearchRepository::class);
        $entry = $repo->findOneByPlayerAndSlug($player, 'mining_efficiency_1');
        self::assertSame(2, $entry->getLevel());
    }

    private function seedPlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 5000));

        $b = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
        $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($b);

        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }
}
