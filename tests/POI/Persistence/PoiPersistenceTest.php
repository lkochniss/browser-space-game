<?php

declare(strict_types=1);

namespace App\Tests\POI\Persistence;

use App\POI\Model\Poi;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class PoiPersistenceTest extends IntegrationTestCase
{
    public function test_poi_persists_with_discriminator_type(): void
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $poi = new Poi(PoiId::generate(), $system, 'Asteroid Belt Alpha');

        $this->em->persist($system);
        $this->em->persist($poi);
        $this->em->flush();

        $poiId = $poi->getId();
        $this->em->clear();

        $reloaded = self::getContainer()->get(PoiRepository::class)->find($poiId);
        self::assertNotNull($reloaded);
        self::assertSame('Asteroid Belt Alpha', $reloaded->getName());
        self::assertNotNull($reloaded->getSolarSystem());
    }

    public function test_solar_system_pois_collection_lazy_loads(): void
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Multi');

        $poi1 = new Poi(PoiId::generate(), $system, 'POI-A');
        $poi2 = new Poi(PoiId::generate(), $system, 'POI-B');

        $this->em->persist($system);
        $this->em->persist($poi1);
        $this->em->persist($poi2);
        $this->em->flush();

        $systemId = $system->getId();
        $this->em->clear();

        $reloadedSystem = $this->em->find(SolarSystem::class, $systemId);
        self::assertCount(2, $reloadedSystem->getPois());
    }

    public function test_repository_find_by_solar_system(): void
    {
        $systemA = new SolarSystem(SolarSystemId::generate(), 'Sol-A');
        $systemB = new SolarSystem(SolarSystemId::generate(), 'Sol-B');

        $poiA = new Poi(PoiId::generate(), $systemA, 'POI-A');
        $poiB1 = new Poi(PoiId::generate(), $systemB, 'POI-B1');
        $poiB2 = new Poi(PoiId::generate(), $systemB, 'POI-B2');

        $this->em->persist($systemA);
        $this->em->persist($systemB);
        $this->em->persist($poiA);
        $this->em->persist($poiB1);
        $this->em->persist($poiB2);
        $this->em->flush();

        $repo = self::getContainer()->get(PoiRepository::class);
        self::assertCount(1, $repo->findBySolarSystem($systemA));
        self::assertCount(2, $repo->findBySolarSystem($systemB));
    }
}
