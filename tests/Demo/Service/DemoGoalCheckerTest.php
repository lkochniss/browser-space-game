<?php

declare(strict_types=1);

namespace App\Tests\Demo\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Demo\Service\DemoGoalChecker;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Research\Repository\PlayerResearchRepository;
use App\Ship\Repository\ShipRepository;
use PHPUnit\Framework\TestCase;

final class DemoGoalCheckerTest extends TestCase
{
    public function test_hub_level_2_goal(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $planet->addBuilding($hub);

        $checker = $this->makeChecker();
        $goals = $checker->check($player);

        self::assertFalse($goals[0]->completed, 'Hub L1 noch nicht complete');
        self::assertStringContainsString('1/2', $goals[0]->progressHint);

        $hub->setLevel(2);
        $goals = $checker->check($player);
        self::assertTrue($goals[0]->completed, 'Hub L2 complete');
    }

    public function test_basic_mines_goal(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::COAL_MINE, 1));

        $goals = $this->makeChecker()->check($player);
        self::assertFalse($goals[1]->completed, '2/3 Mines noch incomplete');
        self::assertStringContainsString('2/3', $goals[1]->progressHint);

        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::COPPER_MINE, 1));
        $goals = $this->makeChecker()->check($player);
        self::assertTrue($goals[1]->completed, '3/3 → complete');
    }

    public function test_recycling_plant_goal(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();

        $goals = $this->makeChecker()->check($player);
        self::assertFalse($goals[2]->completed);

        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::RECYCLING_PLANT, 1));
        $goals = $this->makeChecker()->check($player);
        self::assertTrue($goals[2]->completed);
    }

    public function test_debris_collected_goal(): void
    {
        $player = $this->makePlayer();
        $planet = $player->getPlanets()->first();
        $planet->addResource(Resource::generateWithAmount(ResourceType::DEBRIS_LOW, 30));
        $planet->addResource(Resource::generateWithAmount(ResourceType::DEBRIS_MEDIUM, 10));

        $shipRepo = $this->createMock(ShipRepository::class);
        $shipRepo->method('findByPlanet')->willReturn([]);
        $researchRepo = $this->createMock(PlayerResearchRepository::class);
        $researchRepo->method('findByPlayer')->willReturn([]);
        $checker = new DemoGoalChecker($shipRepo, $researchRepo);

        $goals = $checker->check($player);
        self::assertFalse($goals[3]->completed, '40<50 incomplete');
        self::assertStringContainsString('40/50', $goals[3]->progressHint);

        $planet->addResource(Resource::generateWithAmount(ResourceType::DEBRIS_HIGH, 15));
        $goals = $checker->check($player);
        self::assertTrue($goals[3]->completed, '55>=50 complete');
    }

    public function test_second_planet_goal(): void
    {
        $player = $this->makePlayer();
        $goals = $this->makeChecker()->check($player);
        self::assertFalse($goals[4]->completed, '1 Planet incomplete');

        $planet2 = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet2);
        $goals = $this->makeChecker()->check($player);
        self::assertTrue($goals[4]->completed, '2 Planets complete');
    }

    private function makePlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        return $player;
    }

    private function makeChecker(): DemoGoalChecker
    {
        $shipRepo = $this->createMock(ShipRepository::class);
        $shipRepo->method('findByPlanet')->willReturn([]);
        $researchRepo = $this->createMock(PlayerResearchRepository::class);
        $researchRepo->method('findByPlayer')->willReturn([]);

        return new DemoGoalChecker($shipRepo, $researchRepo);
    }
}
