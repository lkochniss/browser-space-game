<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Common\Interface\CommandBusInterface;
use App\POI\Model\AsteroidField;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\StartSalvageCommand;
use App\Ship\Command\StopSalvageCommand;
use App\Ship\Exception\InvalidSalvageTargetException;
use App\Ship\Exception\NotASalvageShipException;
use App\Ship\Exception\PoiNotFoundException;
use App\Ship\Exception\SalvageTargetNotInSystemException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class SalvageCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_start_salvage_marks_ship_active(): void
    {
        [$ship, $field] = $this->seedSalvageScenario();

        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $field->getId(),
            ResourceType::IRON_ORE,
        ));

        $this->em->clear();
        $reloaded = self::getContainer()->get(ShipRepository::class)->find($ship->getId());

        self::assertTrue($reloaded->isSalvaging());
        self::assertSame($field->getId()->__toString(), $reloaded->getSalvageTargetPoiId());
        self::assertSame(ResourceType::IRON_ORE, $reloaded->getSalvageResourceType());
        self::assertNotNull($reloaded->getSalvageLastTickAt());
    }

    public function test_start_salvage_rejects_non_salvage_ship(): void
    {
        [$ship, $field] = $this->seedSalvageScenario(shipType: ShipType::GENERIC);

        $this->expectException(NotASalvageShipException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $field->getId(),
            ResourceType::IRON_ORE,
        ));
    }

    public function test_start_salvage_rejects_ship_in_different_system(): void
    {
        [$ship, , $otherSystem] = $this->seedSalvageScenarioWithCrossSystem();
        $foreignField = new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $otherSystem,
            contents: [ResourceType::IRON_ORE->value => 1000],
        );
        $this->em->persist($foreignField);
        $this->em->flush();

        $this->expectException(SalvageTargetNotInSystemException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $foreignField->getId(),
            ResourceType::IRON_ORE,
        ));
    }

    public function test_start_salvage_rejects_when_field_lacks_resource(): void
    {
        [$ship, $field] = $this->seedSalvageScenario();

        $this->expectException(InvalidSalvageTargetException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $field->getId(),
            ResourceType::URANIUM_ORE, // not in field
        ));
    }

    public function test_start_salvage_rejects_when_poi_not_asteroid_field(): void
    {
        [$ship, , $system] = $this->seedSalvageScenarioWithCrossSystem();
        $genericPoi = new \App\POI\Model\Poi(PoiId::generate(), $system, 'Other-POI');
        $this->em->persist($genericPoi);
        $this->em->flush();

        $this->expectException(InvalidSalvageTargetException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $genericPoi->getId(),
            ResourceType::IRON_ORE,
        ));
    }

    public function test_stop_salvage_clears_state(): void
    {
        [$ship, $field] = $this->seedSalvageScenario();

        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            $field->getId(),
            ResourceType::IRON_ORE,
        ));
        $this->bus->dispatch(new StopSalvageCommand($ship->getId()));

        $this->em->clear();
        $reloaded = self::getContainer()->get(ShipRepository::class)->find($ship->getId());

        self::assertFalse($reloaded->isSalvaging());
        self::assertNull($reloaded->getSalvageTargetPoiId());
        self::assertNull($reloaded->getSalvageResourceType());
        self::assertNull($reloaded->getSalvageLastTickAt());
    }

    public function test_stop_salvage_idempotent_for_inactive_ship(): void
    {
        [$ship] = $this->seedSalvageScenario();

        $this->bus->dispatch(new StopSalvageCommand($ship->getId()));

        $this->em->clear();
        $reloaded = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertFalse($reloaded->isSalvaging());
    }

    public function test_throws_when_ship_not_found(): void
    {
        $this->expectException(ShipNotFoundException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            ShipId::generate(),
            PoiId::generate(),
            ResourceType::IRON_ORE,
        ));
    }

    public function test_throws_when_poi_not_found(): void
    {
        [$ship] = $this->seedSalvageScenario();

        $this->expectException(PoiNotFoundException::class);
        $this->bus->dispatch(new StartSalvageCommand(
            $ship->getId(),
            PoiId::generate(),
            ResourceType::IRON_ORE,
        ));
    }

    /**
     * @return array{Ship, AsteroidField, SolarSystem}
     */
    private function seedSalvageScenario(ShipType $shipType = ShipType::SALVAGE): array
    {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->setSolarSystem($system);
        $player->claimPlanet($planet);

        $field = new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Test-Field',
            contents: [ResourceType::IRON_ORE->value => 1000],
        );

        $ship = new Ship(
            id: ShipId::generate(),
            type: $shipType,
            populationAssigned: 25,
            cargoVolumeCapacity: 3000,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($field);
        $this->em->persist($ship);
        $this->em->flush();

        return [$ship, $field, $system];
    }

    /**
     * @return array{Ship, AsteroidField, SolarSystem}
     */
    private function seedSalvageScenarioWithCrossSystem(): array
    {
        [$ship, $field, $homeSystem] = $this->seedSalvageScenario();
        $otherSystem = new SolarSystem(SolarSystemId::generate(), 'Sol-Other');
        $this->em->persist($otherSystem);
        $this->em->flush();

        return [$ship, $field, $otherSystem];
    }
}
