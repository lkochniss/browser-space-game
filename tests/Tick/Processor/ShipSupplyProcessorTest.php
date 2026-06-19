<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use App\Tick\Processor\ShipSupplyProcessor;
use DateTimeImmutable;

final class ShipSupplyProcessorTest extends IntegrationTestCase
{
    private ShipSupplyProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $repo = self::getContainer()->get(ShipRepository::class);
        $this->processor = new ShipSupplyProcessor($repo, $this->em);
    }

    public function test_docked_ship_drains_planet_supplies(): void
    {
        $planet = $this->seedPlanetWithDockedShip(water: 10, food: 10, oxygen: 10);

        $this->processor->process($planet, new DateTimeImmutable());
        $this->em->flush();

        self::assertSame(9, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::OXYGEN)->getAmount());
    }

    public function test_falls_back_to_ship_storage_when_planet_partially_empty(): void
    {
        $planet = $this->seedPlanetWithDockedShip(
            water: 0,
            food: 10,
            oxygen: 10,
            shipWater: 30,
            shipFood: 30,
            shipOxygen: 30,
        );

        $this->processor->process($planet, new DateTimeImmutable());
        $this->em->flush();

        // planet WATER war 0 → ship verbraucht 1 vom eigenen Storage
        self::assertSame(0, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(9, $planet->getResource(ResourceType::OXYGEN)->getAmount());

        $ship = self::getContainer()->get(ShipRepository::class)->findByPlanet($planet)[0];
        self::assertSame(29, $ship->getSupplyWater());
        self::assertSame(30, $ship->getSupplyFood());
        self::assertSame(30, $ship->getSupplyOxygen());
    }

    public function test_kills_ship_when_planet_and_ship_storage_empty(): void
    {
        $planet = $this->seedPlanetWithDockedShip(water: 0, food: 0, oxygen: 0);
        $popBefore = $planet->getPopulation()->getTotal();
        $assignedBefore = $planet->getPopulation()->getAssigned();

        $this->processor->process($planet, new DateTimeImmutable());
        $this->em->flush();

        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertCount(0, $repo->findByPlanet($planet));

        // Pop assigned + total reduzieren um 20
        self::assertSame($assignedBefore - 20, $planet->getPopulation()->getAssigned());
        self::assertSame($popBefore - 20, $planet->getPopulation()->getTotal());
    }

    public function test_unfinished_ship_is_skipped(): void
    {
        $planet = $this->seedPlanetWithDockedShip(
            water: 10,
            food: 10,
            oxygen: 10,
            shipFinishedAt: new DateTimeImmutable('+1 hour'),
        );

        $this->processor->process($planet, new DateTimeImmutable());
        $this->em->flush();

        self::assertSame(10, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(10, $planet->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(10, $planet->getResource(ResourceType::OXYGEN)->getAmount());

        $repo = self::getContainer()->get(ShipRepository::class);
        self::assertCount(1, $repo->findByPlanet($planet));
    }

    private function seedPlanetWithDockedShip(
        int $water,
        int $food,
        int $oxygen,
        int $shipWater = 0,
        int $shipFood = 0,
        int $shipOxygen = 0,
        ?DateTimeImmutable $shipFinishedAt = null,
    ): Planet {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, $water));
        $planet->addResource(Resource::generateWithAmount(ResourceType::FOOD, $food));
        $planet->addResource(Resource::generateWithAmount(ResourceType::OXYGEN, $oxygen));

        $planet->getPopulation()->grow(50);
        $planet->getPopulation()->assign(20);

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::GENERIC,
            populationAssigned: 20,
            supplyWater: $shipWater,
            supplyFood: $shipFood,
            supplyOxygen: $shipOxygen,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt($shipFinishedAt);

        $this->em->persist($player);
        $this->em->persist($ship);
        $this->em->flush();

        return $planet;
    }
}
