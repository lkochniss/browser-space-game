<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\POI\Model\AsteroidField;
use App\POI\Model\DebrisField;
use App\POI\Model\Nebula;
use App\POI\Model\Wormhole;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * T-049a World-Fixture: deterministische 5-System-Galaxy für IT-Tests + Local-Setup.
 *
 * UUID-Schema: `49a00000-0000-4000-8000-{12-hex}` (V4-konform, 4=version, 8=variant).
 *  - Systeme:   ...000000000001 .. ...000000000005
 *  - Planeten:  ...0000000000{sysIdx}{idx}
 *  - Asteroids: ...000000000a{sysIdx}
 *  - Nebula:    ...000000000b{sysIdx}
 *  - Wormholes: ...000000000c{sysIdx}
 */
class WorldFixture extends Fixture
{
    public const SYSTEM_ALPHA_ID = '49a00000-0000-4000-8000-000000000001';
    public const SYSTEM_BETA_ID = '49a00000-0000-4000-8000-000000000002';
    public const SYSTEM_GAMMA_ID = '49a00000-0000-4000-8000-000000000003';
    public const SYSTEM_DELTA_ID = '49a00000-0000-4000-8000-000000000004';
    public const SYSTEM_EPSILON_ID = '49a00000-0000-4000-8000-000000000005';

    public const WORMHOLE_ALPHA_ID = '49a00000-0000-4000-8000-0000000000c1';
    public const WORMHOLE_EPSILON_ID = '49a00000-0000-4000-8000-0000000000c5';
    public const DEBRIS_GAMMA_ID = '49a00000-0000-4000-8000-0000000000d3';

    /**
     * @var list<array{id: string, name: string, planets: list<array{id: string, type: PlanetType, size: PlanetSize}>, asteroidId: string, asteroidContents: array<string, int>, nebulaId: ?string}>
     */
    private const SYSTEMS = [
        [
            'id' => self::SYSTEM_ALPHA_ID,
            'name' => 'Sol-Alpha',
            'planets' => [
                ['id' => '49a00000-0000-4000-8000-000000000011', 'type' => PlanetType::TERRAN, 'size' => PlanetSize::MEDIUM],
                ['id' => '49a00000-0000-4000-8000-000000000012', 'type' => PlanetType::DESERT, 'size' => PlanetSize::SMALL],
            ],
            'asteroidId' => '49a00000-0000-4000-8000-0000000000a1',
            'asteroidContents' => ['iron_ore' => 2000, 'copper_ore' => 1500],
            'nebulaId' => null,
        ],
        [
            'id' => self::SYSTEM_BETA_ID,
            'name' => 'Sol-Beta',
            'planets' => [
                ['id' => '49a00000-0000-4000-8000-000000000021', 'type' => PlanetType::ICE, 'size' => PlanetSize::LARGE],
                ['id' => '49a00000-0000-4000-8000-000000000022', 'type' => PlanetType::BARREN, 'size' => PlanetSize::TINY],
            ],
            'asteroidId' => '49a00000-0000-4000-8000-0000000000a2',
            'asteroidContents' => ['silicon' => 1800, 'aluminum_ore' => 1200],
            'nebulaId' => '49a00000-0000-4000-8000-0000000000b2',
        ],
        [
            'id' => self::SYSTEM_GAMMA_ID,
            'name' => 'Sol-Gamma',
            'planets' => [
                ['id' => '49a00000-0000-4000-8000-000000000031', 'type' => PlanetType::GAS_GIANT, 'size' => PlanetSize::HUGE],
                ['id' => '49a00000-0000-4000-8000-000000000032', 'type' => PlanetType::OCEAN, 'size' => PlanetSize::MEDIUM],
                ['id' => '49a00000-0000-4000-8000-000000000033', 'type' => PlanetType::VOLCANIC, 'size' => PlanetSize::SMALL],
            ],
            'asteroidId' => '49a00000-0000-4000-8000-0000000000a3',
            'asteroidContents' => ['titanium_ore' => 1600, 'uranium_ore' => 1100],
            'nebulaId' => null,
        ],
        [
            'id' => self::SYSTEM_DELTA_ID,
            'name' => 'Sol-Delta',
            'planets' => [
                ['id' => '49a00000-0000-4000-8000-000000000041', 'type' => PlanetType::TERRAN, 'size' => PlanetSize::LARGE],
                ['id' => '49a00000-0000-4000-8000-000000000042', 'type' => PlanetType::DESERT, 'size' => PlanetSize::MEDIUM],
            ],
            'asteroidId' => '49a00000-0000-4000-8000-0000000000a4',
            'asteroidContents' => ['coal' => 2500, 'iron_ore' => 1500],
            'nebulaId' => null,
        ],
        [
            'id' => self::SYSTEM_EPSILON_ID,
            'name' => 'Sol-Epsilon',
            'planets' => [
                ['id' => '49a00000-0000-4000-8000-000000000051', 'type' => PlanetType::ICE, 'size' => PlanetSize::SMALL],
                ['id' => '49a00000-0000-4000-8000-000000000052', 'type' => PlanetType::BARREN, 'size' => PlanetSize::HUGE],
            ],
            'asteroidId' => '49a00000-0000-4000-8000-0000000000a5',
            'asteroidContents' => ['uranium_ore' => 1300, 'titanium_ore' => 1700],
            'nebulaId' => null,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $systemsByName = [];

        foreach (self::SYSTEMS as $sysSpec) {
            $system = new SolarSystem(new SolarSystemId($sysSpec['id']), $sysSpec['name']);
            $manager->persist($system);
            $systemsByName[$sysSpec['name']] = $system;

            foreach ($sysSpec['planets'] as $planetSpec) {
                $planet = new Planet(
                    id: new PlanetId($planetSpec['id']),
                    player: null,
                    type: $planetSpec['type'],
                    size: $planetSpec['size'],
                );
                $system->addPlanet($planet);
                $manager->persist($planet);
            }

            foreach (array_keys($sysSpec['asteroidContents']) as $resourceVal) {
                ResourceType::from($resourceVal); // sanity-check at fixture-load
            }
            $asteroid = new AsteroidField(
                id: new PoiId($sysSpec['asteroidId']),
                solarSystem: $system,
                name: sprintf('%s Asteroid Belt', $sysSpec['name']),
                contents: $sysSpec['asteroidContents'],
            );
            $system->addPoi($asteroid);
            $manager->persist($asteroid);

            if ($sysSpec['nebulaId'] !== null) {
                $nebula = new Nebula(
                    id: new PoiId($sysSpec['nebulaId']),
                    solarSystem: $system,
                    name: sprintf('%s Nebula', $sysSpec['name']),
                    concealmentLevel: 7,
                );
                $system->addPoi($nebula);
                $manager->persist($nebula);
            }
        }

        // Wormhole-Pair Sol-Alpha ↔ Sol-Epsilon
        $alpha = $systemsByName['Sol-Alpha'];
        $epsilon = $systemsByName['Sol-Epsilon'];
        $whAlpha = new Wormhole(
            id: new PoiId(self::WORMHOLE_ALPHA_ID),
            solarSystem: $alpha,
            name: 'Wurmloch Sol-Alpha ↔ Sol-Epsilon',
            requiredTechSlug: 'ftl_tier_2',
        );
        $whEpsilon = new Wormhole(
            id: new PoiId(self::WORMHOLE_EPSILON_ID),
            solarSystem: $epsilon,
            name: 'Wurmloch Sol-Epsilon ↔ Sol-Alpha',
            requiredTechSlug: 'ftl_tier_2',
        );
        $whAlpha->pairWith($whEpsilon);
        $alpha->addPoi($whAlpha);
        $epsilon->addPoi($whEpsilon);
        $manager->persist($whAlpha);
        $manager->persist($whEpsilon);

        // T-021: deterministisches DebrisField in Sol-Gamma
        $gamma = $systemsByName['Sol-Gamma'];
        $debris = new DebrisField(
            id: new PoiId(self::DEBRIS_GAMMA_ID),
            solarSystem: $gamma,
            name: 'Sol-Gamma Schlachtfeld',
            contents: [
                ResourceType::DEBRIS_LOW->value => 8,
                ResourceType::DEBRIS_MEDIUM->value => 4,
                ResourceType::DEBRIS_HIGH->value => 1,
            ],
        );
        $gamma->addPoi($debris);
        $manager->persist($debris);

        $manager->flush();
    }
}
