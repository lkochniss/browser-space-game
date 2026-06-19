<?php

declare(strict_types=1);

namespace App\Tests\Probe\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Probe\Command\BuildProbeCommand;
use App\Probe\Exception\InsufficientResourcesException;
use App\Probe\Exception\MissingProbeLabException;
use App\Probe\Exception\PlanetNotFoundException;
use App\Probe\Model\Probe;
use App\Probe\Repository\ProbeRepository;
use App\Probe\ValueObject\ProbeType;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class BuildProbeCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_build_system_probe_succeeds(): void
    {
        $planet = $this->seedPlanetWithLab(ironBar: 100);

        $probe = $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::SYSTEM));

        self::assertInstanceOf(Probe::class, $probe);
        self::assertSame(ProbeType::SYSTEM, $probe->getType());
        self::assertSame($planet->getId(), $probe->getPlanet()?->getId());
        self::assertNotNull($probe->getFinishedAt());

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());

        self::assertSame(70, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());

        $repo = self::getContainer()->get(ProbeRepository::class);
        self::assertCount(1, $repo->findByPlanet($reloaded));
    }

    public function test_build_orbital_probe_consumes_silicon_too(): void
    {
        $planet = $this->seedPlanetWithLab(ironBar: 200, silicon: 100);

        $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::ORBITAL));

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());

        self::assertSame(120, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(70, $reloaded->getResource(ResourceType::SILICON)->getAmount());
    }

    public function test_build_deep_scan_probe_is_endgame_costly(): void
    {
        $planet = $this->seedPlanetWithLab(ironBar: 300, silicon: 100, copperOre: 100);

        $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::DEEP_SCAN));

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());

        self::assertSame(100, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(20, $reloaded->getResource(ResourceType::SILICON)->getAmount());
        self::assertSame(50, $reloaded->getResource(ResourceType::COPPER_ORE)->getAmount());
    }

    public function test_throws_when_planet_not_found(): void
    {
        $this->expectException(PlanetNotFoundException::class);
        $this->bus->dispatch(new BuildProbeCommand(PlanetId::generate(), ProbeType::SYSTEM));
    }

    public function test_throws_when_no_probe_lab(): void
    {
        $planet = $this->seedPlanetWithoutLab(ironBar: 100);

        $this->expectException(MissingProbeLabException::class);
        $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::SYSTEM));
    }

    public function test_throws_when_iron_bar_insufficient(): void
    {
        $planet = $this->seedPlanetWithLab(ironBar: 10);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::SYSTEM));
    }

    public function test_throws_when_silicon_missing_for_orbital(): void
    {
        // ORBITAL braucht 80 Iron-Bar + 30 Silicon. Iron-Bar reicht, Silicon fehlt komplett.
        $planet = $this->seedPlanetWithLab(ironBar: 200);

        $this->expectException(InsufficientResourcesException::class);
        $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::ORBITAL));
    }

    public function test_no_state_change_on_validation_failure(): void
    {
        $planet = $this->seedPlanetWithLab(ironBar: 10);

        try {
            $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::SYSTEM));
        } catch (InsufficientResourcesException) {
            // expected
        }

        $this->em->clear();
        $reloaded = $this->em->find(Planet::class, $planet->getId());
        self::assertSame(10, $reloaded->getResource(ResourceType::IRON_BAR)->getAmount());

        $repo = self::getContainer()->get(ProbeRepository::class);
        self::assertCount(0, $repo->findByPlanet($reloaded));
    }

    private function seedPlanetWithLab(int $ironBar = 0, int $silicon = 0, int $copperOre = 0): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::PROBE_LAB, 1));

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $ironBar));
        if ($silicon > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::SILICON, $silicon));
        }
        if ($copperOre > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::COPPER_ORE, $copperOre));
        }

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }

    private function seedPlanetWithoutLab(int $ironBar): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, $ironBar));

        $this->em->persist($player);
        $this->em->flush();

        return $planet;
    }
}
