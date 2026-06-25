<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\PlayerResearch;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Command\LoadCargoCommand;
use App\Ship\Exception\ShipCargoOverflowException;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

/**
 * T-178: alle Ship-Klassen (Combat + Spezial) haben Cargo-Volumen.
 */
final class ShipCargoUniversalTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_every_non_combat_ship_type_gets_volume_cap(): void
    {
        $expected = [
            ShipType::GENERIC->value => 50,
            ShipType::COLONY_SHIP->value => 300,
            ShipType::TRANSPORT_SMALL->value => 100,
            ShipType::TRANSPORT_MEDIUM->value => 500,
            ShipType::TRANSPORT_LARGE->value => 2000,
            ShipType::SALVAGE->value => 500,
        ];

        foreach ($expected as $typeVal => $cap) {
            $ship = new Ship(
                id: \App\Ship\ValueObject\ShipId::generate(),
                type: ShipType::from($typeVal),
                populationAssigned: 10,
                cargoVolumeCapacity: $cap,
            );

            self::assertSame($cap, $ship->getCargoVolumeCapacity(), "Mismatch for $typeVal");
            self::assertSame($cap, $ship->getCargoVolumeFree());
        }
    }

    public function test_combat_ship_can_load_cargo_t178(): void
    {
        $player = $this->seedCombatReadyPlayer();
        $planet = $player->getPlanets()->first();

        $ship = $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK1,
        ));
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        self::assertSame(50, $ship->getCargoVolumeCapacity());

        $loaded = $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::WATER->value => 30], // 30 m³ → fits in 50
        ));

        self::assertSame(30, $loaded->getCargo()->getResource(ResourceType::WATER));
        self::assertSame(30, $loaded->getCargoVolumeUsed());
    }

    public function test_combat_ship_load_overflow(): void
    {
        $player = $this->seedCombatReadyPlayer();
        $planet = $player->getPlanets()->first();

        $ship = $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK1,
        ));
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->expectException(ShipCargoOverflowException::class);
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [ResourceType::WATER->value => 51], // 51 m³ > 50 m³
        ));
    }

    private function seedCombatReadyPlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 1000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::STEEL, 2000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 200));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 1));
        $planet->getPopulation()->grow(100);

        // Captain (idle) für Combat-Build
        $captain = new Crew(
            id: CrewId::generate(),
            owner: $player,
            type: CrewType::CAPTAIN,
            status: CrewStatus::IDLE,
        );

        // Research Hydrogen propulsion (foundation = no research needed)
        $this->em->persist($player);
        $this->em->persist($captain);
        $this->em->flush();

        return $player;
    }
}
