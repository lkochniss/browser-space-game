<?php

declare(strict_types=1);

namespace App\Tests\Discovery\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\AdjustableClock;
use App\Discovery\Model\PlayerSystemDiscovery;
use App\Discovery\Repository\PlayerSystemDiscoveryRepository;
use App\Discovery\Service\TelescopeDiscoveryService;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\Repository\SolarSystemRepository;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class TelescopeDiscoveryServiceTest extends IntegrationTestCase
{
    public function test_no_telescope_no_discovery(): void
    {
        [$player, ] = $this->seedPlayerWithSystems(systemCount: 3, telescopeLevel: 0);

        $service = $this->makeService();
        $revealed = $service->runTickForPlayer($player);

        self::assertSame(0, $revealed);
    }

    public function test_level_1_reveals_one_system(): void
    {
        [$player, $systems] = $this->seedPlayerWithSystems(systemCount: 4, telescopeLevel: 1);

        $service = $this->makeService();
        $revealed = $service->runTickForPlayer($player);

        self::assertSame(1, $revealed);

        /** @var PlayerSystemDiscoveryRepository $repo */
        $repo = self::getContainer()->get(PlayerSystemDiscoveryRepository::class);
        // 1 Heimat-System + 1 neu = 2 entdeckt
        self::assertCount(2, $repo->findByPlayer($player));
    }

    public function test_level_3_reveals_three_systems(): void
    {
        [$player, ] = $this->seedPlayerWithSystems(systemCount: 5, telescopeLevel: 3);

        $service = $this->makeService();
        $revealed = $service->runTickForPlayer($player);

        self::assertSame(3, $revealed);
    }

    public function test_caps_at_unknown_count(): void
    {
        // 3 Systeme total, alle bereits entdeckt → Tick kann nichts mehr aufdecken
        [$player, $systems] = $this->seedPlayerWithSystems(systemCount: 3, telescopeLevel: 5);

        // Manuell alle markieren
        $service = $this->makeService();
        foreach ($systems as $sys) {
            $service->markDiscovered($player, $sys);
        }
        $this->em->flush();

        $revealed = $service->runTickForPlayer($player);
        self::assertSame(0, $revealed, 'alle entdeckt → kein Reveal');
    }

    public function test_mark_discovered_idempotent(): void
    {
        [$player, $systems] = $this->seedPlayerWithSystems(systemCount: 1, telescopeLevel: 0);

        $service = $this->makeService();
        $service->markDiscovered($player, $systems[0]);
        $service->markDiscovered($player, $systems[0]);
        $this->em->flush();

        /** @var PlayerSystemDiscoveryRepository $repo */
        $repo = self::getContainer()->get(PlayerSystemDiscoveryRepository::class);
        self::assertCount(1, $repo->findByPlayer($player), 'duplicate markDiscovered = no-op');
    }

    /**
     * @return array{0: Player, 1: list<SolarSystem>}
     */
    private function seedPlayerWithSystems(int $systemCount, int $telescopeLevel): array
    {
        $player = new Player(PlayerId::generate());
        $homePlanet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($homePlanet);

        $homeSystem = new SolarSystem(SolarSystemId::generate(), 'Sol-Home');
        $homeSystem->addPlanet($homePlanet);

        $systems = [$homeSystem];
        for ($i = 1; $i < $systemCount; $i++) {
            $sys = new SolarSystem(SolarSystemId::generate(), sprintf('Sol-%d', $i));
            $systems[] = $sys;
        }

        if ($telescopeLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::TELESCOPE, $telescopeLevel);
            $b->setFinishedAt(new DateTimeImmutable('-1 minute'));
            $homePlanet->addBuilding($b);
        }

        foreach ($systems as $sys) {
            $this->em->persist($sys);
        }
        $this->em->persist($player);
        $this->em->flush();

        // Initial-Discovery: Heimat-System
        $service = $this->makeService();
        $service->markDiscovered($player, $homeSystem);
        $this->em->flush();

        return [$player, $systems];
    }

    private function makeService(): TelescopeDiscoveryService
    {
        return self::getContainer()->get(TelescopeDiscoveryService::class);
    }
}
