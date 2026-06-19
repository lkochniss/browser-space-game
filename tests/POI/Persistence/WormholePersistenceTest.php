<?php

declare(strict_types=1);

namespace App\Tests\POI\Persistence;

use App\POI\Model\Wormhole;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class WormholePersistenceTest extends IntegrationTestCase
{
    public function test_paired_wormholes_persist_with_twin_reference(): void
    {
        $systemA = new SolarSystem(SolarSystemId::generate(), 'Sol-A');
        $systemB = new SolarSystem(SolarSystemId::generate(), 'Sol-B');

        $wA = new Wormhole(PoiId::generate(), $systemA, 'WH-A', 'ftl_tier_2');
        $wB = new Wormhole(PoiId::generate(), $systemB, 'WH-B', 'ftl_tier_2');
        $wA->pairWith($wB);

        $this->em->persist($systemA);
        $this->em->persist($systemB);
        $this->em->persist($wA);
        $this->em->persist($wB);
        $this->em->flush();

        $idA = $wA->getId();
        $idB = $wB->getId();
        $this->em->clear();

        $repo = self::getContainer()->get(PoiRepository::class);
        $reloadedA = $repo->find($idA);
        $reloadedB = $repo->find($idB);

        self::assertInstanceOf(Wormhole::class, $reloadedA);
        self::assertInstanceOf(Wormhole::class, $reloadedB);
        self::assertSame($idB->__toString(), $reloadedA->getTwin()->getId()->__toString());
        self::assertSame($idA->__toString(), $reloadedB->getTwin()->getId()->__toString());
        self::assertSame('ftl_tier_2', $reloadedA->getRequiredTechSlug());
    }
}
