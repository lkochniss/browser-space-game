<?php

declare(strict_types=1);

namespace App\Tests\Research\Model\Prerequisite;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\Prerequisite\BuildingLevelPrerequisite;
use App\Research\Model\Prerequisite\PlayerResearchLookup;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildingLevelPrerequisiteTest extends TestCase
{
    public function test_unmet_when_player_has_no_buildings(): void
    {
        $player = $this->makePlayer();
        $prereq = new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 1);

        self::assertFalse($prereq->isMetBy($player, new DateTimeImmutable(), $this->lookup()));
    }

    public function test_met_when_building_at_required_level_and_ready(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 2);
        $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($b);

        $prereq = new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 2);
        self::assertTrue($prereq->isMetBy($player, new DateTimeImmutable(), $this->lookup()));
    }

    public function test_unmet_when_building_not_ready(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $b->setFinishedAt(new DateTimeImmutable('+1 hour'));
        $planet->addBuilding($b);

        $prereq = new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 1);
        self::assertFalse($prereq->isMetBy($player, new DateTimeImmutable(), $this->lookup()));
    }

    public function test_unmet_when_level_too_low(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
        $planet->addBuilding($b);

        $prereq = new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 2);
        self::assertFalse($prereq->isMetBy($player, new DateTimeImmutable(), $this->lookup()));
    }

    public function test_describe_human_readable(): void
    {
        $prereq = new BuildingLevelPrerequisite(BuildingType::IRON_MINE, 3);
        self::assertSame('Building iron_mine L3', $prereq->describe());
    }

    private function makePlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        return $player;
    }

    private function lookup(): PlayerResearchLookup
    {
        return new class implements PlayerResearchLookup {
            public function getPlayerResearchLevel(Player $p, string $s): int
            {
                return 0;
            }
        };
    }
}
