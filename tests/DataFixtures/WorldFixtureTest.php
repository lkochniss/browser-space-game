<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\DataFixtures\WorldFixture;
use App\POI\Model\AsteroidField;
use App\POI\Model\Nebula;
use App\POI\Model\Wormhole;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\Repository\SolarSystemRepository;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class WorldFixtureTest extends IntegrationTestCase
{
    public function test_loads_5_systems_with_planets_and_pois(): void
    {
        $fixture = new WorldFixture();
        $fixture->load($this->em);
        $this->em->clear();

        /** @var SolarSystemRepository $sysRepo */
        $sysRepo = self::getContainer()->get(SolarSystemRepository::class);
        /** @var PlanetRepository $planetRepo */
        $planetRepo = self::getContainer()->get(PlanetRepository::class);
        /** @var PoiRepository $poiRepo */
        $poiRepo = self::getContainer()->get(PoiRepository::class);

        $systems = $sysRepo->findAll();
        self::assertCount(5, $systems, '5 SolarSystems erwartet');

        $planets = $planetRepo->findAll();
        self::assertCount(11, $planets, '2+2+3+2+2=11 Planets erwartet');

        $pois = $poiRepo->findAll();

        $asteroids = array_filter($pois, fn ($p) => $p instanceof AsteroidField);
        self::assertCount(5, $asteroids, '1 Asteroid pro System');

        $wormholes = array_filter($pois, fn ($p) => $p instanceof Wormhole);
        self::assertCount(2, $wormholes, 'Wormhole-Pair (2 endpoints)');

        $nebulae = array_filter($pois, fn ($p) => $p instanceof Nebula);
        self::assertCount(1, $nebulae, '1 Nebula in Sol-Beta');
    }

    public function test_fixed_uuids_resolvable(): void
    {
        $fixture = new WorldFixture();
        $fixture->load($this->em);
        $this->em->clear();

        /** @var SolarSystemRepository $sysRepo */
        $sysRepo = self::getContainer()->get(SolarSystemRepository::class);
        $alpha = $sysRepo->find(new SolarSystemId(WorldFixture::SYSTEM_ALPHA_ID));
        self::assertInstanceOf(SolarSystem::class, $alpha);
        self::assertSame('Sol-Alpha', $alpha->getName());

        /** @var PoiRepository $poiRepo */
        $poiRepo = self::getContainer()->get(PoiRepository::class);
        $whAlpha = $poiRepo->find(new PoiId(WorldFixture::WORMHOLE_ALPHA_ID));
        self::assertInstanceOf(Wormhole::class, $whAlpha);
        $twin = $whAlpha->getTwin();
        self::assertInstanceOf(Wormhole::class, $twin);
        self::assertSame(WorldFixture::WORMHOLE_EPSILON_ID, (string) $twin->getId());
    }

    public function test_asteroid_contents_deterministic(): void
    {
        $fixture = new WorldFixture();
        $fixture->load($this->em);
        $this->em->clear();

        /** @var PoiRepository $poiRepo */
        $poiRepo = self::getContainer()->get(PoiRepository::class);

        // Sol-Alpha Asteroid: iron_ore=2000, copper_ore=1500
        $alphaAsteroid = $poiRepo->find(new PoiId('49a00000-0000-4000-8000-0000000000a1'));
        self::assertInstanceOf(AsteroidField::class, $alphaAsteroid);
        $contents = $alphaAsteroid->getContents();
        self::assertSame(2000, $contents['iron_ore'] ?? 0);
        self::assertSame(1500, $contents['copper_ore'] ?? 0);
    }

    public function test_planets_unclaimed(): void
    {
        $fixture = new WorldFixture();
        $fixture->load($this->em);
        $this->em->clear();

        /** @var PlanetRepository $planetRepo */
        $planetRepo = self::getContainer()->get(PlanetRepository::class);
        $planets = $planetRepo->findAll();

        foreach ($planets as $planet) {
            self::assertNull($planet->getPlayer(), sprintf('Planet %s sollte unclaimed sein', $planet->getId()));
        }
    }
}
