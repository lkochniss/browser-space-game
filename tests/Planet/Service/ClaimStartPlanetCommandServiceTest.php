<?php

declare(strict_types=1);

namespace App\Tests\Planet\Service;

use App\Planet\Service\ClaimStartPlanetCommandService;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class ClaimStartPlanetCommandServiceTest extends IntegrationTestCase
{
    public function test_start_planet_has_iron_ore_resource_at_zero(): void
    {
        $player = $this->claimFreshPlayer();
        $planet = $player->getPlanets()->first();

        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(1000, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_start_planet_has_three_renewables_at_100(): void
    {
        $player = $this->claimFreshPlayer();
        $planet = $player->getPlanets()->first();

        self::assertSame(100, $planet->getResource(ResourceType::WATER)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::FOOD)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::OXYGEN)->getAmount());
    }

    public function test_renewables_have_no_deposits(): void
    {
        $player = $this->claimFreshPlayer();
        $planet = $player->getPlanets()->first();

        self::assertNull($planet->getResourceDeposits()->filter(
            fn ($d) => $d->getResourceType() === ResourceType::WATER
        )->first() ?: null);

        self::assertSame(1, $planet->getResourceDeposits()->count());
    }

    public function test_resources_persisted_after_claim(): void
    {
        $player = $this->claimFreshPlayer();
        $playerId = $player->getId();

        $this->em->clear();

        $reloaded = $this->em->find(Player::class, $playerId);
        $planet = $reloaded->getPlanets()->first();

        self::assertSame(4, $planet->getResources()->count());
        self::assertSame(100, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_start_planet_population_is_50_of_125_cap(): void
    {
        // T-172: ClaimStartPlanet baut HQ L1 (+25 Cap) und IRON_MINE auto.
        $player = $this->claimFreshPlayer();
        $planet = $player->getPlanets()->first();

        $pop = $planet->getPopulation();
        self::assertSame(50, $pop->getTotal());
        self::assertSame(0, $pop->getAssigned());
        self::assertSame(125, $pop->getCap());
        self::assertSame(50, $pop->getFree());
    }

    public function test_galaxy_has_5_systems(): void
    {
        $this->claimFreshPlayer();

        $repo = $this->em->getRepository(\App\SolarSystem\Model\SolarSystem::class);
        $allSystems = $repo->findAll();

        self::assertCount(5, $allSystems);

        // Each system has exactly 1 planet
        foreach ($allSystems as $system) {
            self::assertCount(1, $system->getPlanets(), 'System ' . $system->getName());
        }
    }

    public function test_start_planet_belongs_to_a_system(): void
    {
        $player = $this->claimFreshPlayer();
        $startPlanet = $player->getPlanets()->first();

        self::assertNotNull($startPlanet->getSolarSystem());
        self::assertStringStartsWith('Sol-', $startPlanet->getSolarSystem()->getName());
    }

    public function test_only_start_planet_has_player(): void
    {
        $this->claimFreshPlayer();

        $allPlanets = $this->em->getRepository(\App\Planet\Model\Planet::class)->findAll();
        $owned = array_filter($allPlanets, fn ($p) => $p->getPlayer() !== null);

        self::assertCount(5, $allPlanets, '5 planets total (1 per system)');
        self::assertCount(1, $owned, 'only the start planet is claimed');
    }

    public function test_start_planet_is_terran_medium(): void
    {
        $player = $this->claimFreshPlayer();
        $planet = $player->getPlanets()->first();

        self::assertSame(\App\Planet\ValueObject\PlanetType::TERRAN, $planet->getType());
        self::assertSame(\App\Planet\ValueObject\PlanetSize::MEDIUM, $planet->getSize());
    }

    public function test_other_planets_have_random_type_and_size(): void
    {
        $this->claimFreshPlayer();

        $allPlanets = $this->em->getRepository(\App\Planet\Model\Planet::class)->findAll();
        $unowned = array_filter($allPlanets, fn ($p) => $p->getPlayer() === null);

        self::assertCount(4, $unowned);
        foreach ($unowned as $planet) {
            self::assertInstanceOf(\App\Planet\ValueObject\PlanetType::class, $planet->getType());
            self::assertInstanceOf(\App\Planet\ValueObject\PlanetSize::class, $planet->getSize());
        }
    }

    public function test_t085_wormhole_pair_spawns_in_galaxy(): void
    {
        $this->claimFreshPlayer();

        $repo = self::getContainer()->get(\App\POI\Repository\PoiRepository::class);
        $wormholes = array_values(array_filter(
            $repo->findAll(),
            fn ($poi) => $poi instanceof \App\POI\Model\Wormhole,
        ));

        self::assertCount(2, $wormholes, '1 wormhole-pair = 2 POI-rows');
        self::assertNotNull($wormholes[0]->getTwin());
        self::assertNotNull($wormholes[1]->getTwin());

        $ids = [$wormholes[0]->getId()->__toString(), $wormholes[1]->getId()->__toString()];
        $twinIds = [
            $wormholes[0]->getTwin()->getId()->__toString(),
            $wormholes[1]->getTwin()->getId()->__toString(),
        ];
        sort($ids);
        sort($twinIds);
        self::assertSame($ids, $twinIds, 'twins reference each other bidirectionally');

        self::assertNotSame(
            $wormholes[0]->getSolarSystem()->getId()->__toString(),
            $wormholes[1]->getSolarSystem()->getId()->__toString(),
            'wormhole-pair connects 2 different systems',
        );

        self::assertSame('ftl_warp', $wormholes[0]->getRequiredTechSlug());
    }

    public function test_t020_asteroid_fields_spawn_with_finite_resources(): void
    {
        // Multiple Runs damit RNG-Variabilität nicht zu false-negative führt
        $totalFields = 0;
        for ($run = 0; $run < 10; $run++) {
            $this->setUp();
            $this->claimFreshPlayer();
            $repo = self::getContainer()->get(\App\POI\Repository\PoiRepository::class);
            $fields = array_filter(
                $repo->findAll(),
                fn ($poi) => $poi instanceof \App\POI\Model\AsteroidField,
            );
            $totalFields += count($fields);
            foreach ($fields as $field) {
                self::assertGreaterThan(0, $field->getTotalAmount(), 'asteroid field should have content at spawn');
                foreach ($field->getContents() as $resourceValue => $amount) {
                    $type = \App\Resource\ValueObject\ResourceType::from($resourceValue);
                    self::assertSame(
                        \App\Resource\ValueObject\ResourceCategory::FINITE,
                        $type->getCategory(),
                        sprintf('Only FINITE resources allowed in asteroids, got %s', $resourceValue),
                    );
                    self::assertGreaterThanOrEqual(500, $amount);
                    self::assertLessThanOrEqual(2000, $amount);
                }
            }
        }
        // Über 10 Runs × 5 Systeme × max 2 Felder = max 100; im Schnitt ~50.
        // Mind. 1 Feld erwartet — ansonsten ist Spawn-Logic broken.
        self::assertGreaterThan(0, $totalFields, 'expected at least 1 asteroid field across 10 runs');
    }

    private function claimFreshPlayer(): Player
    {
        $service = self::getContainer()->get(ClaimStartPlanetCommandService::class);

        return $service(PlayerId::generate(), PlanetId::generate());
    }
}
