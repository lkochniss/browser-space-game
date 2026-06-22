<?php

declare(strict_types=1);

namespace App\Tests\Research\Model;

use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\ActiveResearch;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

/**
 * T-025c: JSON-Roundtrip für booster_planet_ids + primary_planet_id.
 */
final class ActiveResearchPersistenceTest extends IntegrationTestCase
{
    public function test_persists_primary_and_booster_planet_ids(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        $primary = PlanetId::generate();
        $boosters = [PlanetId::generate(), PlanetId::generate()];

        $now = new DateTimeImmutable('2026-06-22 12:00:00');
        $entry = ActiveResearch::generate(
            $player,
            'basic_mining',
            1,
            $now,
            $now->modify('+10 minutes'),
            $primary,
            $boosters,
        );
        $this->em->persist($entry);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->getRepository(ActiveResearch::class)->find($entry->getId());
        self::assertNotNull($reloaded);
        self::assertSame((string) $primary, $reloaded->getPrimaryPlanetId());
        self::assertSame(
            array_map(static fn (PlanetId $id): string => (string) $id, $boosters),
            $reloaded->getBoosterPlanetIds(),
        );
    }

    public function test_persists_with_no_boosters(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        $now = new DateTimeImmutable('2026-06-22 12:00:00');
        $entry = ActiveResearch::generate(
            $player,
            'basic_mining',
            1,
            $now,
            $now->modify('+5 minutes'),
            PlanetId::generate(),
            [],
        );
        $this->em->persist($entry);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->getRepository(ActiveResearch::class)->find($entry->getId());
        self::assertNotNull($reloaded);
        self::assertSame([], $reloaded->getBoosterPlanetIds());
    }
}
