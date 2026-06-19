<?php

declare(strict_types=1);

namespace App\Tests\SolarSystem\Model;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use PHPUnit\Framework\TestCase;

final class SolarSystemTest extends TestCase
{
    public function test_generate_creates_name_with_id_prefix(): void
    {
        $id = new SolarSystemId('7a3f5b9c-1234-5678-9abc-def012345678');
        $system = SolarSystem::generate($id);

        self::assertSame('Sol-7A3F', $system->getName());
        self::assertTrue($system->getId()->equals($id));
    }

    public function test_add_planet_sets_inverse_side(): void
    {
        $system = SolarSystem::generate(SolarSystemId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());

        $system->addPlanet($planet);

        self::assertSame($system, $planet->getSolarSystem());
        self::assertCount(1, $system->getPlanets());
    }

    public function test_adding_same_planet_twice_is_idempotent(): void
    {
        $system = SolarSystem::generate(SolarSystemId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());

        $system->addPlanet($planet);
        $system->addPlanet($planet);

        self::assertCount(1, $system->getPlanets());
    }
}
