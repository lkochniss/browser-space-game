<?php

declare(strict_types=1);

namespace App\Planet\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use Doctrine\ORM\EntityManagerInterface;

class ClaimStartPlanetCommandService
{
    private const RENEWABLE_START_AMOUNT = 100;
    private const START_POPULATION = 50;
    private const START_GALAXY_SYSTEM_COUNT = 5;
    private const START_IRON_DEPOSIT = 1000;

    /** @var ResourceType[] */
    private const RENEWABLES = [
        ResourceType::WATER,
        ResourceType::FOOD,
        ResourceType::OXYGEN,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(PlayerId $playerId, PlanetId $planetId): Player
    {
        $player = new Player($playerId);

        // Start-Planet: hard TERRAN + MEDIUM für stabiles Onboarding
        $startPlanet = Planet::generatePlanet($planetId, PlanetType::TERRAN, PlanetSize::MEDIUM);
        $player->claimPlanet($startPlanet);

        $this->seedStartPlanet($startPlanet);

        $systems = $this->generateGalaxy($startPlanet);

        foreach ($systems as $system) {
            $this->em->persist($system);
        }
        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }

    /**
     * @return SolarSystem[]
     */
    private function generateGalaxy(Planet $startPlanet): array
    {
        $systems = [];

        $startSystem = SolarSystem::generate(SolarSystemId::generate());
        $startSystem->addPlanet($startPlanet);
        $systems[] = $startSystem;

        for ($i = 1; $i < self::START_GALAXY_SYSTEM_COUNT; $i++) {
            $system = SolarSystem::generate(SolarSystemId::generate());
            $type = $this->randomEnum(PlanetType::cases());
            $size = $this->randomEnum(PlanetSize::cases());
            $unowned = Planet::generatePlanet(PlanetId::generate(), $type, $size);
            $this->seedRandomPlanet($unowned);
            $system->addPlanet($unowned);
            $systems[] = $system;
        }

        return $systems;
    }

    private function seedStartPlanet(Planet $planet): void
    {
        $now = $this->clock->now();

        // T-062: Start-Mine ist instant ready (finishedAt = jetzt) → kein Bauzeit-Wait beim Onboarding
        $startMine = Building::createNewBuilding(BuildingType::IRON_MINE);
        $startMine->setFinishedAt($now);
        $planet->addBuilding($startMine, $now);

        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        foreach (self::RENEWABLES as $renewable) {
            $planet->addResource(Resource::generateWithAmount($renewable, self::RENEWABLE_START_AMOUNT));
        }

        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, self::START_IRON_DEPOSIT));

        $planet->getPopulation()->grow(self::START_POPULATION);
    }

    private function seedRandomPlanet(Planet $planet): void
    {
        $deposits = $planet->getType()->generateDeposits($planet->getSize());
        foreach ($deposits as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(
                ResourceType::from($resourceTypeValue),
                $amount,
            ));
        }
    }

    /**
     * @template T of \UnitEnum
     * @param T[] $cases
     * @return T
     */
    private function randomEnum(array $cases)
    {
        return $cases[array_rand($cases)];
    }
}
