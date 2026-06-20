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
use App\Research\Exception\AlreadyResearchingException;
use App\Research\Exception\InsufficientResearchResourcesException;
use App\Research\Exception\MaxLevelReachedException;
use App\Research\Exception\ResearchLabMissingException;
use App\Research\Exception\ResearchNodeNotFoundException;
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

        $active = $this->makeStartService()->__invoke($player->getId(), 'basic_mining');

        self::assertSame('basic_mining', $active->getNodeSlug());
        self::assertSame(1, $active->getTargetLevel());

        // basic_mining baseDuration 240s, Lab L1 = no boost
        $deltaSec = $active->getFinishedAt()->getTimestamp() - $active->getStartedAt()->getTimestamp();
        self::assertSame(240, $deltaSec);
    }

    public function test_lab_missing_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 0);

        $this->expectException(ResearchLabMissingException::class);
        $this->makeStartService()->__invoke($player->getId(), 'basic_mining');
    }

    public function test_already_researching_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1);
        $service = $this->makeStartService();
        $service->__invoke($player->getId(), 'basic_mining');

        $this->expectException(AlreadyResearchingException::class);
        $service->__invoke($player->getId(), 'basic_mining');
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
        $this->makeStartService()->__invoke($player->getId(), 'basic_mining');
    }

    public function test_max_level_reached(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 5);
        $service = $this->makeStartService();
        $completion = $this->makeCompletionService();

        // basic_mining maxLevel = 1 — nach 1× research soll 2. Versuch fehlschlagen
        $service->__invoke($player->getId(), 'basic_mining');
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(99999);
        $completion->runTickForPlayer($player);

        $this->expectException(MaxLevelReachedException::class);
        $service->__invoke($player->getId(), 'basic_mining');
    }

    public function test_multi_lab_aggregates_with_diminishing_returns(): void
    {
        // T-025b: 3 Labs L1 → effective 1+0.5+0.25 = 1.75
        $player = $this->seedPlayerWithLab(labLevel: 0);
        $planet = $player->getPlanets()->first();
        for ($i = 0; $i < 3; $i++) {
            $b = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
            $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
            $planet->addBuilding($b);
        }
        $this->em->flush();

        $effective = $this->makeStartService()->getEffectiveLabLevel($player, new DateTimeImmutable());

        self::assertEqualsWithDelta(1.75, $effective, 0.01);
    }

    public function test_single_lab_l3_beats_three_l1_labs(): void
    {
        $playerSingle = $this->seedPlayerWithLab(labLevel: 3);
        $effSingle = $this->makeStartService()->getEffectiveLabLevel($playerSingle, new DateTimeImmutable());

        $playerMulti = $this->seedPlayerWithLab(labLevel: 0);
        $planet = $playerMulti->getPlanets()->first();
        for ($i = 0; $i < 3; $i++) {
            $b = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
            $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
            $planet->addBuilding($b);
        }
        $this->em->flush();
        $effMulti = $this->makeStartService()->getEffectiveLabLevel($playerMulti, new DateTimeImmutable());

        self::assertGreaterThan($effMulti, $effSingle, 'Single L3 (3.0) > 3×L1 (1.75)');
    }

    public function test_lab_higher_level_reduces_duration(): void
    {
        $playerWithBigLab = $this->seedPlayerWithLab(labLevel: 5);
        $active = $this->makeStartService()->__invoke($playerWithBigLab->getId(), 'basic_mining');

        $delta = $active->getFinishedAt()->getTimestamp() - $active->getStartedAt()->getTimestamp();
        // 240 / 1.18^4 ≈ 123
        self::assertLessThan(240, $delta);
        self::assertGreaterThan(100, $delta);
    }

    public function test_resources_deducted(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 1, ironAmount: 500);
        $this->makeStartService()->__invoke($player->getId(), 'basic_mining');
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->getRepository(Player::class)->find($player->getId());
        $totalIron = 0;
        foreach ($reloaded->getPlanets() as $planet) {
            foreach ($planet->getResources() as $r) {
                if ($r->getType() === ResourceType::IRON_ORE) {
                    $totalIron += $r->getAmount();
                }
            }
        }
        // basic_mining-Cost: 100 IRON_ORE
        self::assertSame(400, $totalIron);
    }

    /**
     * Seed minimal: Lab + IRON_MINE L1 (Building-Prereq für basic_mining).
     */
    private function seedPlayerWithLab(int $labLevel, int $ironAmount = 1000): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $ironAmount));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 500));
        $planet->addResource(Resource::generateWithAmount(ResourceType::SILICON, 200));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 300));

        $now = new DateTimeImmutable('-1 minute');
        if ($labLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, $labLevel);
            $b->setFinishedAt($now);
            $planet->addBuilding($b);
        }

        // basic_mining-Building-Prereq IRON_MINE L1
        $mine = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $mine->setFinishedAt($now);
        $planet->addBuilding($mine);

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
