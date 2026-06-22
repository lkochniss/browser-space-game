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
use App\Research\Exception\InvalidLabSelectionException;
use App\Research\Exception\LabLevelTooLowException;
use App\Research\Exception\MaxLevelReachedException;
use App\Research\Exception\ResearchLabMissingException;
use App\Research\Exception\ResearchNodeNotFoundException;
use App\Research\Model\PlayerResearch;
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

    public function test_list_ready_labs_returns_all_lab_planets(): void
    {
        // T-025c: Service exponiert listReadyLabs für Demo-CLI/UI.
        $player = $this->seedPlayerWithLab(labLevel: 2);
        $labs = $this->makeStartService()->listReadyLabs($player, new DateTimeImmutable());

        self::assertCount(1, $labs);
        self::assertSame(2, $labs[0]['labLevel']);
    }

    public function test_multi_lab_opt_in_persists_primary_and_boosters(): void
    {
        $player = $this->seedPlayerWithLabAndExtraPlanet(primaryLvl: 3, boosterLvl: 2);

        $planets = iterator_to_array($player->getPlanets());
        $primary = $planets[0];
        $booster = $planets[1];

        $active = $this->makeStartService()->__invoke(
            $player->getId(),
            'basic_mining',
            $primary->getId(),
            [$booster->getId()],
        );

        self::assertSame((string) $primary->getId(), $active->getPrimaryPlanetId());
        self::assertSame([(string) $booster->getId()], $active->getBoosterPlanetIds());
    }

    public function test_multi_lab_speeds_up_duration_and_costs_more(): void
    {
        // Single L3 vs L3 + Booster L3 → effective 4.5 → schneller + 10% Aufschlag
        $playerSingle = $this->seedPlayerWithLab(labLevel: 3);
        $singleActive = $this->makeStartService()->__invoke($playerSingle->getId(), 'basic_mining');
        $singleDuration = $singleActive->getFinishedAt()->getTimestamp() - $singleActive->getStartedAt()->getTimestamp();

        $playerMulti = $this->seedPlayerWithLabAndExtraPlanet(primaryLvl: 3, boosterLvl: 3, ironAmount: 1000);
        $planets = iterator_to_array($playerMulti->getPlanets());
        $activeMulti = $this->makeStartService()->__invoke(
            $playerMulti->getId(),
            'basic_mining',
            $planets[0]->getId(),
            [$planets[1]->getId()],
        );
        $multiDuration = $activeMulti->getFinishedAt()->getTimestamp() - $activeMulti->getStartedAt()->getTimestamp();

        self::assertLessThan($singleDuration, $multiDuration, 'Multi-Lab → schneller');
    }

    public function test_booster_not_owned_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 2);
        $foreignId = PlanetId::generate();

        $this->expectException(InvalidLabSelectionException::class);
        $primary = $player->getPlanets()->first();
        $this->makeStartService()->__invoke($player->getId(), 'basic_mining', $primary->getId(), [$foreignId]);
    }

    public function test_booster_without_ready_lab_throws(): void
    {
        // 2. Planet ohne Lab → InvalidLabSelectionException
        $player = $this->seedPlayerWithLabAndExtraPlanet(primaryLvl: 2, boosterLvl: 0);
        $planets = iterator_to_array($player->getPlanets());

        $this->expectException(InvalidLabSelectionException::class);
        $this->makeStartService()->__invoke(
            $player->getId(),
            'basic_mining',
            $planets[0]->getId(),
            [$planets[1]->getId()],
        );
    }

    public function test_primary_in_booster_list_throws(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 2);
        $primary = $player->getPlanets()->first();

        $this->expectException(InvalidLabSelectionException::class);
        $this->makeStartService()->__invoke(
            $player->getId(),
            'basic_mining',
            $primary->getId(),
            [$primary->getId()],
        );
    }

    public function test_auto_picks_strongest_lab_when_no_primary_given(): void
    {
        // 2 Planeten, einer mit L3, einer mit L1 → Auto-Pick = L3-Planet
        $player = $this->seedPlayerWithLabAndExtraPlanet(primaryLvl: 1, boosterLvl: 3);
        $planets = iterator_to_array($player->getPlanets());
        // primary = planets[0] (L1), booster = planets[1] (L3) — Auto-Pick wählt L3
        $active = $this->makeStartService()->__invoke($player->getId(), 'basic_mining');

        self::assertSame((string) $planets[1]->getId(), $active->getPrimaryPlanetId());
        self::assertSame([], $active->getBoosterPlanetIds());
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

    public function test_tier_2_node_rejected_with_lab_l1(): void
    {
        // T-069: propulsion_ion ist Tier-2 (requiredLabLevel=2). Lab L1 reicht nicht.
        $player = $this->seedPlayerWithLab(labLevel: 1);
        // Prereq erfüllen
        $this->grantResearch($player, 'shipbuilding', 1);
        $this->grantResearch($player, 'propulsion_hydrogen', 1);
        $this->addShipyard($player, level: 1);
        $this->addExtraResources($player);

        $this->expectException(LabLevelTooLowException::class);
        $this->makeStartService()->__invoke($player->getId(), 'propulsion_ion');
    }

    public function test_tier_2_node_accepted_with_lab_l2(): void
    {
        $player = $this->seedPlayerWithLab(labLevel: 2);
        $this->grantResearch($player, 'shipbuilding', 1);
        $this->grantResearch($player, 'propulsion_hydrogen', 1);
        $this->addShipyard($player, level: 1);
        $this->addExtraResources($player);

        $active = $this->makeStartService()->__invoke($player->getId(), 'propulsion_ion');

        self::assertSame('propulsion_ion', $active->getNodeSlug());
    }

    public function test_tier_3_node_rejected_with_lab_l2(): void
    {
        // T-069: propulsion_antimatter ist Tier-3 (requiredLabLevel=3). Lab L2 reicht nicht.
        $player = $this->seedPlayerWithLab(labLevel: 2);
        $this->grantResearch($player, 'shipbuilding', 1);
        $this->grantResearch($player, 'propulsion_hydrogen', 1);
        $this->grantResearch($player, 'propulsion_ion', 1);
        $this->grantResearch($player, 'propulsion_fusion', 1);
        $this->addShipyard($player, level: 1);
        $this->addExtraResources($player);

        $this->expectException(LabLevelTooLowException::class);
        $this->makeStartService()->__invoke($player->getId(), 'propulsion_antimatter');
    }

    public function test_booster_lifts_effective_lab_above_required(): void
    {
        // T-025c × T-069: Primary L2 + Booster L2 → effective 3.0 → reicht für Tier-3
        $player = $this->seedPlayerWithLabAndExtraPlanet(primaryLvl: 2, boosterLvl: 2);
        $this->grantResearch($player, 'shipbuilding', 1);
        $this->grantResearch($player, 'propulsion_hydrogen', 1);
        $this->grantResearch($player, 'propulsion_ion', 1);
        $this->grantResearch($player, 'propulsion_fusion', 1);
        $this->addShipyard($player, level: 1);
        $this->addExtraResources($player);

        $planets = iterator_to_array($player->getPlanets());
        $active = $this->makeStartService()->__invoke(
            $player->getId(),
            'propulsion_antimatter',
            $planets[0]->getId(),
            [$planets[1]->getId()],
        );

        self::assertSame('propulsion_antimatter', $active->getNodeSlug());
    }

    private function grantResearch(Player $player, string $slug, int $level): void
    {
        $this->em->persist(PlayerResearch::generate($player, $slug, $level));
        $this->em->flush();
    }

    private function addShipyard(Player $player, int $level): void
    {
        $planet = $player->getPlanets()->first();
        $shipyard = new Building(BuildingId::generate(), BuildingType::SHIPYARD, $level);
        $shipyard->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($shipyard);
        $this->em->flush();
    }

    private function addExtraResources(Player $player): void
    {
        $planet = $player->getPlanets()->first();
        $planet->addResource(Resource::generateWithAmount(ResourceType::COPPER_ORE, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::TITANIUM_ORE, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::URANIUM_ORE, 5000));
        $this->em->flush();
    }

    /**
     * Seed Player mit 2 Planeten: Primary mit Lab/IRON_MINE + Booster optional mit Lab.
     */
    private function seedPlayerWithLabAndExtraPlanet(int $primaryLvl, int $boosterLvl, int $ironAmount = 1000): Player
    {
        $player = new Player(PlayerId::generate());
        $primary = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($primary);
        $primary->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, $ironAmount));
        $primary->addResource(Resource::generateWithAmount(ResourceType::COAL, 500));
        $primary->addResource(Resource::generateWithAmount(ResourceType::SILICON, 200));
        $primary->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 300));

        $now = new DateTimeImmutable('-1 minute');
        if ($primaryLvl > 0) {
            $primaryLab = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, $primaryLvl);
            $primaryLab->setFinishedAt($now);
            $primary->addBuilding($primaryLab);
        }
        $mine = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $mine->setFinishedAt($now);
        $primary->addBuilding($mine);

        $booster = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($booster);
        if ($boosterLvl > 0) {
            $boosterLab = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, $boosterLvl);
            $boosterLab->setFinishedAt($now);
            $booster->addBuilding($boosterLab);
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
