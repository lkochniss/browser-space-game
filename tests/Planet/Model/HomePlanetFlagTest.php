<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-081: Heimat-Schutz-Foundation. Pure Flag-Test:
 *  - Default false
 *  - markAsHome() idempotent
 *  - Persistence-Roundtrip
 */
final class HomePlanetFlagTest extends IntegrationTestCase
{
    public function test_planet_defaults_to_non_home(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());

        self::assertFalse($planet->isHomePlanet());
    }

    public function test_mark_as_home_sets_flag(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->markAsHome();

        self::assertTrue($planet->isHomePlanet());
    }

    public function test_mark_as_home_idempotent(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->markAsHome();
        $planet->markAsHome();

        self::assertTrue($planet->isHomePlanet());
    }

    public function test_home_flag_persists_roundtrip(): void
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->markAsHome();
        $player->claimPlanet($planet);

        $this->em->persist($player);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->find(Planet::class, $planet->getId());
        self::assertNotNull($reloaded);
        self::assertTrue($reloaded->isHomePlanet());
    }
}
