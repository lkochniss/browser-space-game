<?php

declare(strict_types=1);

namespace App\Tests\Ship\Command;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Crew\Command\StartCrewTrainingCommand;
use App\Crew\Repository\CrewRepository;
use App\Crew\Service\CrewTrainingCompletionService;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Research\Model\PlayerResearch;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Exception\MissingCaptainException;
use App\Ship\Exception\MissingShipyardLevelException;
use App\Ship\Exception\ShipClassResearchNotMetException;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\PropulsionType;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-102 Combat-Schiff-Bau via ShipClass-Parameter. Validiert Shipyard-Level,
 * Mark-Research und Captain-Verfügbarkeit zusätzlich zu Resources + Pop.
 */
final class BuildCombatShipTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_frigate_mk1_builds_with_shipyard_l1_captain_and_resources(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: true);

        $ship = $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK1,
        ));

        self::assertInstanceOf(Ship::class, $ship);
        self::assertSame(ShipClass::FRIGATE_MK1, $ship->getShipClass());
        self::assertTrue($ship->isCombatShip());
        self::assertSame(30, $ship->getEscapePodSurvivalChance());
    }

    public function test_destroyer_mk1_requires_shipyard_l3(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: true);

        $this->expectException(MissingShipyardLevelException::class);
        $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::DESTROYER_MK1,
        ));
    }

    public function test_combat_ship_without_captain_throws(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: false);

        $this->expectException(MissingCaptainException::class);
        $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK1,
        ));
    }

    public function test_frigate_mk2_requires_research(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: true);

        $this->expectException(ShipClassResearchNotMetException::class);
        $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK2,
        ));
    }

    public function test_frigate_mk2_builds_when_research_granted(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: true);
        $this->em->persist(PlayerResearch::generate($planet->getPlayer(), 'frigate_mk2', 1));
        $this->em->flush();

        $ship = $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK2,
        ));

        self::assertSame(ShipClass::FRIGATE_MK2, $ship->getShipClass());
    }

    public function test_combat_ship_keeps_ship_type_generic(): void
    {
        $planet = $this->seedCombatPlanet(shipyardLevel: 1, withCaptain: true);

        $ship = $this->bus->dispatch(new BuildShipCommand(
            planetId: $planet->getId(),
            shipClass: ShipClass::FRIGATE_MK1,
        ));

        self::assertSame(ShipType::GENERIC, $ship->getType());
    }

    private function seedCombatPlanet(int $shipyardLevel, bool $withCaptain): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        // Genug Resources + Pop für FRIGATE_MK1 (500 Steel, 200 IronBar, 30 Pop)
        $planet->addResource(Resource::generateWithAmount(ResourceType::STEEL, 10000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 5000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::CHIP, 1000));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COMPOSITE, 500));
        $planet->addResource(Resource::generateWithAmount(ResourceType::HULL_PLATE, 200));
        $planet->getPopulation()->grow(500);

        if ($shipyardLevel > 0) {
            $planet->addBuilding(new Building(
                BuildingId::generate(),
                BuildingType::SHIPYARD,
                $shipyardLevel,
            ));
        }
        // Academy + Officer-Quarters für Captain-Provisioning
        if ($withCaptain) {
            $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::ACADEMY, 1));
            $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::OFFICER_QUARTERS, 1));
        }

        $this->em->persist($player);
        $this->em->flush();

        if ($withCaptain) {
            $this->provisionIdleCaptain($player);
        }

        return $planet;
    }

    private function provisionIdleCaptain(Player $player): void
    {
        $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));
        // Advance clock past training duration + tick.
        $clock = self::getContainer()->get(\App\Common\Service\AdjustableClock::class);
        $clock->advance(new \DateInterval('PT2H'));
        self::getContainer()->get(CrewTrainingCompletionService::class)->runTick();
    }
}
