<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Exception\BuildingLockedException;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\AdjustableClock;
use App\Faction\Service\FactionSeedService;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Research\Command\StartResearchCommand;
use App\Research\Exception\PrerequisiteNotMetException;
use App\Research\Service\ResearchCompletionService;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * T-170 E2E: bauen-Tier0 → forschen-Tier1 → bauen-Tier1 als ehrlicher Flow
 * mit ClaimStartPlanetCommand-Bootstrap + echtem CommandBus-Dispatch.
 */
final class TechTreeE2ETest extends IntegrationTestCase
{
    public function test_locked_building_cannot_be_built_without_research(): void
    {
        $player = $this->bootstrapPlayer();
        $planet = $player->getPlanets()->first();

        // Planet bekommt Resources um Cost-Check NICHT zu blocken
        $this->seedAbundantResources($planet);

        $bus = self::getContainer()->get(\App\Common\Interface\CommandBusInterface::class);

        // COAL_MINE ist gated by basic_mining → muss locked sein
        $this->expectException(BuildingLockedException::class);
        $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::COAL_MINE));
    }

    public function test_research_blocked_without_building_prereq(): void
    {
        $player = $this->bootstrapPlayer();
        $planet = $player->getPlanets()->first();
        $this->seedAbundantResources($planet);

        // RESEARCH_LAB hinzufügen (sonst Lab-Missing-Exception früher)
        $lab = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
        $lab->setFinishedAt(new \DateTimeImmutable('-1 minute'));
        $planet->addBuilding($lab);
        $this->em->flush();

        $bus = self::getContainer()->get(\App\Common\Interface\CommandBusInterface::class);

        // metallurgy braucht IRON_MINE L2 (currently-has-ready) — Player hat keine Mine
        $this->expectException(PrerequisiteNotMetException::class);
        $bus->dispatch(new StartResearchCommand($player->getId(), 'metallurgy'));
    }

    public function test_e2e_chain_iron_mine_then_research_then_coal_mine(): void
    {
        $player = $this->bootstrapPlayer();
        $planet = $player->getPlanets()->first();
        $this->seedAbundantResources($planet);

        $bus = self::getContainer()->get(\App\Common\Interface\CommandBusInterface::class);

        // 1. RESEARCH_LAB + IRON_MINE bauen (beide Tier-0)
        $lab = new Building(BuildingId::generate(), BuildingType::RESEARCH_LAB, 1);
        $lab->setFinishedAt(new \DateTimeImmutable('-1 minute'));
        $planet->addBuilding($lab);
        $mine = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $mine->setFinishedAt(new \DateTimeImmutable('-1 minute'));
        $planet->addBuilding($mine);
        $this->em->flush();

        // 2. basic_mining forschen (braucht IRON_MINE L1 ✓)
        $bus->dispatch(new StartResearchCommand($player->getId(), 'basic_mining'));

        // 3. Forschung mechanisch abschließen
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(99999);
        self::getContainer()->get(ResearchCompletionService::class)->runTickForPlayer($player);

        // 4. Jetzt darf COAL_MINE gebaut werden
        $built = $bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::COAL_MINE));
        self::assertSame(BuildingType::COAL_MINE, $built->getType());
    }

    private function bootstrapPlayer(): \App\Player\Model\Player
    {
        self::getContainer()->get(FactionSeedService::class)->seed();
        $bus = self::getContainer()->get(\App\Common\Interface\CommandBusInterface::class);
        $playerId = PlayerId::generate();
        $bus->dispatch(new ClaimStartPlanetCommand($playerId, PlanetId::generate()));

        $player = self::getContainer()->get(PlayerRepository::class)->find($playerId);

        return $player;
    }

    private function seedAbundantResources(\App\Planet\Model\Planet $planet): void
    {
        // Stelle sicher dass ALLE Resources reichlich vorhanden sind (Cost-Check abdecken).
        foreach ([
            ResourceType::IRON_ORE,
            ResourceType::COAL,
            ResourceType::COPPER_ORE,
            ResourceType::SILICON,
            ResourceType::IRON_BAR,
        ] as $r) {
            try {
                $planet->getResource($r)->setAmount(10000);
            } catch (\Throwable) {
                $planet->addResource(Resource::generateWithAmount($r, 10000));
            }
        }
        // Pop hochsetzen
        $planet->getPopulation()->grow(500);
        $this->em->flush();
    }
}
