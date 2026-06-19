<?php

declare(strict_types=1);

namespace App\Tests\POI\Persistence;

use App\POI\Model\Nebula;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class NebulaPersistenceTest extends IntegrationTestCase
{
    public function test_nebula_persists_and_loads_as_subtype(): void
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $nebula = new Nebula(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Crab Nebula',
            concealmentLevel: 7,
        );

        $this->em->persist($system);
        $this->em->persist($nebula);
        $this->em->flush();

        $nebulaId = $nebula->getId();
        $this->em->clear();

        $reloaded = self::getContainer()->get(PoiRepository::class)->find($nebulaId);
        self::assertInstanceOf(Nebula::class, $reloaded);
        self::assertSame(7, $reloaded->getConcealmentLevel());
        self::assertSame('Crab Nebula', $reloaded->getName());
    }
}
