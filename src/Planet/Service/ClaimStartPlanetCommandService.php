<?php

declare(strict_types=1);

namespace App\Planet\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\Discovery\Service\TelescopeDiscoveryService;
use App\POI\Model\AsteroidField;
use App\POI\Model\Nebula;
use App\POI\Model\Wormhole;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\ValueObject\ResourceCategory;
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

    // T-020: 0-2 Asteroidenfelder pro System, jeweils 1-3 zufällige FINITE-Resources mit 500-2000 Amount
    private const ASTEROID_FIELD_MAX_PER_SYSTEM = 2;
    private const ASTEROID_FIELD_MAX_RESOURCE_TYPES = 3;
    private const ASTEROID_FIELD_AMOUNT_MIN = 500;
    private const ASTEROID_FIELD_AMOUNT_MAX = 2000;

    // T-022: 30% Chance pro System für 1 Nebel mit zufälligem Concealment 3-9
    private const NEBULA_SPAWN_CHANCE_PERCENT = 30;
    private const NEBULA_CONCEALMENT_MIN = 3;
    private const NEBULA_CONCEALMENT_MAX = 9;

    // T-085: 1 Wurmloch-Pair pro Galaxy (verbindet 2 zufällige unterschiedliche Systems)
    private const WORMHOLE_PAIRS_PER_GALAXY = 1;
    private const WORMHOLE_REQUIRED_TECH_SLUG = 'ftl_tier_2';

    /** @var ResourceType[] */
    private const RENEWABLES = [
        ResourceType::WATER,
        ResourceType::FOOD,
        ResourceType::OXYGEN,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClockInterface $clock,
        private readonly TelescopeDiscoveryService $telescopeDiscovery,
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

        // T-018: Heimat-System sofort als entdeckt markieren
        $homeSystem = $startPlanet->getSolarSystem();
        if ($homeSystem !== null) {
            $this->telescopeDiscovery->markDiscovered($player, $homeSystem);
            $this->em->flush();
        }

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
        $this->generateAsteroidFields($startSystem);
        $this->maybeGenerateNebula($startSystem);
        $systems[] = $startSystem;

        for ($i = 1; $i < self::START_GALAXY_SYSTEM_COUNT; $i++) {
            $system = SolarSystem::generate(SolarSystemId::generate());
            $type = $this->randomEnum(PlanetType::cases());
            $size = $this->randomEnum(PlanetSize::cases());
            $unowned = Planet::generatePlanet(PlanetId::generate(), $type, $size);
            $this->seedRandomPlanet($unowned);
            $system->addPlanet($unowned);
            $this->generateAsteroidFields($system);
            $this->maybeGenerateNebula($system);
            $systems[] = $system;
        }

        $this->generateWormholePairs($systems);

        return $systems;
    }

    /**
     * T-085: erzeugt N Wurmloch-Pairs zwischen zufälligen unterschiedlichen
     * Systems der Galaxy. Pro Pair: 2 Wurmloch-POIs, bidirektional via twin
     * verlinkt. Tech-Lock-Slug ist als Stub gesetzt — T-026 FTL-Tier-2 prüft
     * das später.
     *
     * @param SolarSystem[] $systems
     */
    private function generateWormholePairs(array $systems): void
    {
        if (count($systems) < 2) {
            return;
        }

        for ($p = 0; $p < self::WORMHOLE_PAIRS_PER_GALAXY; $p++) {
            $shuffled = $systems;
            shuffle($shuffled);
            $systemA = $shuffled[0];
            $systemB = $shuffled[1];

            $wormholeA = new Wormhole(
                id: PoiId::generate(),
                solarSystem: $systemA,
                name: sprintf('Wurmloch %s ↔ %s', $systemA->getName(), $systemB->getName()),
                requiredTechSlug: self::WORMHOLE_REQUIRED_TECH_SLUG,
            );
            $wormholeB = new Wormhole(
                id: PoiId::generate(),
                solarSystem: $systemB,
                name: sprintf('Wurmloch %s ↔ %s', $systemB->getName(), $systemA->getName()),
                requiredTechSlug: self::WORMHOLE_REQUIRED_TECH_SLUG,
            );
            $wormholeA->pairWith($wormholeB);

            $systemA->addPoi($wormholeA);
            $systemB->addPoi($wormholeB);
        }
    }

    /**
     * T-020: 0..MAX zufällige Asteroidenfelder pro System mit 1..MAX zufälligen
     * FINITE-Resources im Bereich [MIN_AMOUNT, MAX_AMOUNT].
     */
    private function generateAsteroidFields(SolarSystem $system): void
    {
        $fieldCount = random_int(0, self::ASTEROID_FIELD_MAX_PER_SYSTEM);
        if ($fieldCount === 0) {
            return;
        }

        $finiteResources = array_filter(
            ResourceType::cases(),
            static fn (ResourceType $r) => $r->getCategory() === ResourceCategory::FINITE,
        );
        $finiteResources = array_values($finiteResources);

        for ($i = 0; $i < $fieldCount; $i++) {
            $resourceCount = random_int(1, min(self::ASTEROID_FIELD_MAX_RESOURCE_TYPES, count($finiteResources)));
            shuffle($finiteResources);
            $contents = [];
            for ($j = 0; $j < $resourceCount; $j++) {
                $contents[$finiteResources[$j]->value] = random_int(
                    self::ASTEROID_FIELD_AMOUNT_MIN,
                    self::ASTEROID_FIELD_AMOUNT_MAX,
                );
            }

            $field = new AsteroidField(
                id: PoiId::generate(),
                solarSystem: $system,
                name: sprintf('Asteroid Belt #%d', $i + 1),
                contents: $contents,
            );
            $system->addPoi($field);
        }
    }

    /**
     * T-022: 30% Chance, dass im System ein Nebel spawnt mit zufälligem
     * Concealment-Level [3, 9].
     */
    private function maybeGenerateNebula(SolarSystem $system): void
    {
        if (random_int(1, 100) > self::NEBULA_SPAWN_CHANCE_PERCENT) {
            return;
        }

        $nebula = new Nebula(
            id: PoiId::generate(),
            solarSystem: $system,
            name: sprintf('Nebel %s', $system->getName()),
            concealmentLevel: random_int(
                self::NEBULA_CONCEALMENT_MIN,
                self::NEBULA_CONCEALMENT_MAX,
            ),
        );
        $system->addPoi($nebula);
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
