<?php

declare(strict_types=1);

namespace App\Tests\Research\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\AdjustableClock;
use App\Common\Service\SystemClock;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Research\Command\StartResearchCommand;
use App\Research\Exception\AlreadyResearchingException;
use App\Research\Exception\InsufficientResearchResourcesException;
use App\Research\Exception\MaxLevelReachedException;
use App\Research\Exception\ResearchLabMissingException;
use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use App\Research\Service\ResearchCompletionService;
use App\Research\Service\StartResearchCommandService;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class StartResearchCommandServiceTest extends IntegrationTestCase
{
    public function test_start_simple_research_persists_active(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1);
        $service = $this->makeStartService();

        $active = $service->__invoke($player->getId(), 'mining_efficiency_1');

        self::assertSame('mining_efficiency_1', $active->getNodeSlug());
        self::assertSame(1, $active->getTargetLevel());

        // mining_efficiency_1 L1 base = 300s, Lab L1 = no boost → finished_at = now + 300s
        $deltaSec = $active->getFinishedAt()->getTimestamp() - $active->getStartedAt()->getTimestamp();
        self::assertSame(300, $deltaSec);
    }

    public function test_lab_missing_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 0);

        $this->expectException(ResearchLabMissingException::class);
        $this->makeStartService()->__invoke($player->getId(), 'mining_efficiency_1');
    }

    public function test_already_researching_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1);
        $service = $this->makeStartService();
        $service->__invoke($player->getId(), 'mining_efficiency_1');

        $this->expectException(AlreadyResearchingException::class);
        $service->__invoke($player->getId(), 'ftl_tier_1');
    }

    public function test_unknown_node_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1);

        $this->expectException(ResearchNodeNotFoundException::class);
        $this->makeStartService()->__invoke($player->getId(), 'fake_slug');
    }

    public function test_insufficient_resources_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1, ironAmount: 0);

        $this->expectException(InsufficientResearchResourcesException::class);
        $this->makeStartService()->__invoke($player->getId(), 'mining_efficiency_1');
    }

    public function test_max_level_reached(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 5);
        $service = $this->makeStartService();
        $completion = $this->makeCompletionService();

        // ftl_tier_1 maxLevel = 1 — nach 1× research soll 2. Versuch fehlschlagen
        $service->__invoke($player->getId(), 'ftl_tier_1');
        // Manuell completion forcen via clock advance + runTick
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(99999);
        $completion->runTickForPlayer($player);

        $this->expectException(MaxLevelReachedException::class);
        $service->__invoke($player->getId(), 'ftl_tier_1');
    }

    public function test_lab_higher_level_reduces_duration(): void
    {
        $playerWithBigLab = $this->seedPlayerWithLab(labLevel: 5);
        $active = $this->makeStartService()->__invoke($playerWithBigLab->getId(), 'mining_efficiency_1');

        $delta = $active->getFinishedAt()->getTimestamp() - $active->getStartedAt()->getTimestamp();
        // 300 / 1.18^4 ≈ 154
        self::assertLessThan(300, $delta);
        self::assertGreaterThan(150, $delta);
    }

    public function test_resources_deducted(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1, ironAmount: 500, coalAmount: 200);
        $this->makeStartService()->__invoke($player->getId(), 'mining_efficiency_1');
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->getRepository(Player::class)->find($player->getId());
        $totalIron = 0;
        $totalCoal = 0;
        foreach ($reloaded->getPlanets() as $planet) {
            foreach ($planet->getResources() as $r) {
                if ($r->getType() === ResourceType::IRON_ORE) {
                    $totalIron += $r->getAmount();
                }
                if ($r->getType() === ResourceType::COAL) {
                    $totalCoal += $r->getAmount();
                }
            }
        }
        // Cost: 100 iron + 50 coal abgezogen
        self::assertSame(400, $totalIron);
        self::assertSame(150, $totalCoal);
    }

    private function seedPlayerWithLab(int $labLevel, int $ironAmount = 1000, int $coalAmount = 500): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $ironAmount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, $coalAmount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::SILICON, 200));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 300));

        if ($labLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, $labLevel);
            $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
            $planet->addBuilding($b);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }

    private function makeStartService(): StartResearchCommandService
    {
        return self::getContainer()->get(StartResearchCommandService::class);
    }

    private function makeCompletionService(): ResearchCompletionService
    {
        return self::getContainer()->get(ResearchCompletionService::class);
    }
}
